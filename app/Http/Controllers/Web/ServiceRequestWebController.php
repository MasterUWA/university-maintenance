<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\ServiceRequest;
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
        return view('requests.index', [
            'requests' => $this->visibleRequests($request->user())
                ->with(['category', 'requester'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
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

        return view('requests.show', ['request' => $serviceRequest]);
    }

    public function adminIndex(Request $request): View
    {
        return $this->index($request);
    }

    public function officerIndex(Request $request): View
    {
        return $this->index($request);
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
