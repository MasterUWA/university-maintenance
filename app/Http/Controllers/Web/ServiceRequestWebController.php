<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\StatusUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceRequestWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->visibleRequests($request->user())->with(['category', 'requester']);
        $this->applySearch($query, $request);

        return view('requests.index', [
            'requests' => $query->latest()->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    private function applySearch($query, Request $request): void
    {
        $term = trim((string) $request->query('search', ''));
        $like = '%'.addcslashes($term, '%_\\').'%';

        $query
            ->when($term !== '', fn ($q) => $q->where(function ($q) use ($like, $term) {
                $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('location', 'like', $like)
                    ->orWhereHas('requester', fn ($r) => $r->where('name', 'like', $like))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like));
                if (ctype_digit(ltrim($term, '#'))) {
                    $q->orWhere('id', (int) ltrim($term, '#'));
                }
            }))
            ->when(in_array($request->query('status'), ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED', 'CANCELLED'], true),
                fn ($q) => $q->where('status', $request->query('status')))
            ->when(in_array($request->query('priority'), ['LOW', 'MEDIUM', 'HIGH', 'URGENT'], true),
                fn ($q) => $q->where('priority', $request->query('priority')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', (int) $request->query('category_id')))
            ->when($request->boolean('overdue'), fn ($q) => $q
                ->whereIn('status', ServiceRequest::OPEN_STATUSES)->where('due_at', '<', now()));
    }

    public function create(): View
    {
        return view('requests.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:LOW,MEDIUM,HIGH,URGENT'],
            'category_id' => ['required', 'exists:categories,id'],
            'evidence.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $data['requester_id'] = $request->user()->id;
        $serviceRequest = ServiceRequest::create($data);

        foreach ($request->file('evidence', []) as $file) {
            $serviceRequest->attachments()->create([
                'path' => $file->store('request-evidence', 'public'),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'CREATE_REQUEST',
            'entity' => 'ServiceRequest',
            'entity_id' => $serviceRequest->id,
        ]);

        User::role('ADMIN')->each(fn ($admin) => Notification::create([
            'user_id' => $admin->id,
            'title' => 'New service request',
            'message' => "Request #{$serviceRequest->id} needs review.",
        ]));

        return redirect()->route('requests.show', $serviceRequest)
            ->with('status', 'request-created');
    }

    public function show(Request $request, int $id): View
    {
        $serviceRequest = $this->visibleRequests($request->user())
            ->with(['category', 'requester', 'assignments.officer', 'statusUpdates.officer', 'attachments'])
            ->findOrFail($id);

        $comments = $serviceRequest->comments()->with('user')
            ->when($request->user()->hasRole('STUDENT_STAFF'), fn ($query) => $query->where('is_internal', false))
            ->get();

        return view('requests.show', [
            'request' => $serviceRequest,
            'comments' => $comments,
            'officers' => $request->user()->hasRole('ADMIN') ? User::role('OFFICER')->orderBy('name')->get() : collect(),
            'canUpdateStatus' => $this->canUpdateStatus($request->user(), $serviceRequest),
        ]);
    }

    public function adminIndex(Request $request): View
    {
        return $this->index($request);
    }

    public function officerIndex(Request $request): View
    {
        $assignments = ServiceRequest::query()
            ->whereHas('assignments', fn ($query) => $query->where('officer_id', $request->user()->id))
            ->whereIn('status', ServiceRequest::OPEN_STATUSES)
            ->with(['category', 'requester'])
            ->tap(fn ($q) => $this->applySearch($q, $request))
            ->orderByRaw("CASE priority WHEN 'URGENT' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 ELSE 4 END")
            ->orderBy('due_at')
            ->get();

        return view('requests.officer-assignments', [
            'assignments' => $assignments,
            'overdueCount' => $assignments->filter->isOverdue()->count(),
            'atRiskCount' => $assignments->filter->isAtRisk()->count(),
        ]);
    }

    public function assign(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $data = $request->validate([
            'officer_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $officer = User::findOrFail($data['officer_id']);
        abort_unless($officer->hasRole('OFFICER'), 422, 'The selected user is not an officer.');

        $serviceRequest->assignTo($officer, $request->user(), $data['notes'] ?? null);

        return redirect()->route('requests.show', $serviceRequest)->with('status', "Assigned to {$officer->name}.");
    }

    public function autoAssign(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $officer = User::leastBusyOfficer();

        if (! $officer) {
            return redirect()->route('requests.show', $serviceRequest)->with('status', 'No officers are available to assign.');
        }

        $serviceRequest->assignTo($officer, $request->user(), 'Auto-assigned to the officer with the lightest workload.');

        return redirect()->route('requests.show', $serviceRequest)->with('status', "Auto-assigned to {$officer->name}.");
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = ServiceRequest::with('assignments')->findOrFail($id);
        abort_unless($this->canUpdateStatus($request->user(), $serviceRequest), 403);

        $data = $request->validate([
            'status' => ['required', 'in:ASSIGNED,IN_PROGRESS,COMPLETED,REJECTED,CANCELLED'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        StatusUpdate::create([
            'request_id' => $serviceRequest->id,
            'officer_id' => $request->user()->id,
            'status' => $data['status'],
            'comment' => $data['comment'] ?? null,
        ]);
        $serviceRequest->update(['status' => $data['status']]);

        Notification::create([
            'user_id' => $serviceRequest->requester_id,
            'title' => 'Request Update',
            'message' => "Your request #{$serviceRequest->id} is now {$data['status']}.",
        ]);

        return redirect()->route('requests.show', $serviceRequest)->with('status', 'Status updated.');
    }

    public function comment(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = $this->visibleRequests($request->user())->with('assignments')->findOrFail($id);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $isInternal = ! $request->user()->hasRole('STUDENT_STAFF') && $request->boolean('is_internal');

        $serviceRequest->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_internal' => $isInternal,
        ]);

        $recipients = $request->user()->id === $serviceRequest->requester_id
            ? $serviceRequest->assignments->pluck('officer_id')->unique()
            : ($isInternal ? collect() : collect([$serviceRequest->requester_id]));

        foreach ($recipients->reject(fn ($userId) => $userId === $request->user()->id) as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => 'New comment',
                'message' => "{$request->user()->name} commented on request #{$serviceRequest->id}.",
            ]);
        }

        return redirect()->route('requests.show', $serviceRequest)->with('status', 'Comment added.');
    }

    public function rate(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        abort_unless($serviceRequest->requester_id === $request->user()->id, 403);
        abort_unless($serviceRequest->status === 'COMPLETED' && $serviceRequest->rating === null, 422, 'This request cannot be rated.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'rating_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $serviceRequest->fill([
            'rating' => $data['rating'],
            'rating_comment' => $data['rating_comment'] ?? null,
            'rated_at' => now(),
        ]);

        if ($request->boolean('reopen')) {
            $serviceRequest->status = 'PENDING';
            $serviceRequest->due_at = now()->addHours(ServiceRequest::SLA_HOURS[$serviceRequest->priority] ?? 336);
            $serviceRequest->escalated_at = null;

            User::role('ADMIN')->each(fn ($admin) => Notification::create([
                'user_id' => $admin->id,
                'title' => 'Request reopened',
                'message' => "Request #{$serviceRequest->id} was reopened by the requester.",
            ]));
        }

        $serviceRequest->save();

        return redirect()->route('requests.show', $serviceRequest)->with('status', 'Thanks for your feedback.');
    }

    private function canUpdateStatus($user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole('ADMIN')
            || ($user->hasRole('OFFICER') && $serviceRequest->assignments->contains('officer_id', $user->id));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:LOW,MEDIUM,HIGH,URGENT'],
            'status' => ['required', 'in:PENDING,ASSIGNED,IN_PROGRESS,COMPLETED,REJECTED,CANCELLED'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $serviceRequest->update($data);
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE_REQUEST',
            'entity' => 'ServiceRequest',
            'entity_id' => $serviceRequest->id,
        ]);

        return redirect()->route('requests.show', $serviceRequest)->with('status', 'request-updated');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'DELETE_REQUEST',
            'entity' => 'ServiceRequest',
            'entity_id' => $serviceRequest->id,
        ]);
        $serviceRequest->delete();

        return redirect()->route('admin.requests')->with('status', 'request-deleted');
    }

    public function export(Request $request): StreamedResponse
    {
        $requests = $this->visibleRequests($request->user())
            ->with(['category', 'requester'])
            ->latest()
            ->get();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'EXPORT_REQUESTS_CSV',
            'entity' => 'ServiceRequest',
            'entity_id' => 0,
        ]);

        return response()->streamDownload(function () use ($requests) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Category', 'Location', 'Priority', 'Status', 'Requester', 'Created At']);
            foreach ($requests as $serviceRequest) {
                fputcsv($handle, [
                    $serviceRequest->id,
                    $serviceRequest->title,
                    $serviceRequest->category?->name,
                    $serviceRequest->location,
                    $serviceRequest->priority,
                    $serviceRequest->status,
                    $serviceRequest->requester?->name,
                    $serviceRequest->created_at?->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, 'service-requests.csv', ['Content-Type' => 'text/csv']);
    }

    private function visibleRequests($user)
    {
        $query = ServiceRequest::query();

        if ($user->hasRole('STUDENT_STAFF')) {
            $query->where('requester_id', $user->id);
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($nested) use ($search) {
                $nested->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
