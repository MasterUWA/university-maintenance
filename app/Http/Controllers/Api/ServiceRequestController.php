<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Assignment;
use App\Models\StatusUpdate;
use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = ServiceRequest::with(['category', 'requester:id,name,email', 'assignments.officer:id,name']);

        // Role-based visibility
        if ($user->hasRole('STUDENT_STAFF')) {
            $query->where('requester_id', $user->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        $data = $query->latest()->paginate($request->input('limit', 10));

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => [
                'page'  => $data->currentPage(),
                'limit' => $data->perPage(),
                'total' => $data->total(),
                'pages' => $data->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string',
            'priority'    => 'in:LOW,MEDIUM,HIGH,URGENT',
            'category_id' => 'required|exists:categories,id',
        ]);

        $data['requester_id'] = $request->user()->id;

        $req = ServiceRequest::create($data);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'action'    => 'CREATE_REQUEST',
            'entity'    => 'ServiceRequest',
            'entity_id' => $req->id,
        ]);

        User::whereHas('roles', fn ($role) => $role->where('name', 'ADMIN'))->each(fn ($admin) => Notification::create([
            'user_id' => $admin->id,
            'title' => 'New service request',
            'message' => "Request #{$req->id} needs review.",
        ]));

        return response()->json(['success' => true, 'data' => $req->load('category')], 201);
    }

    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        if ($user->hasRole('STUDENT_STAFF') && $serviceRequest->requester_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $serviceRequest->load([
                'category', 'requester:id,name,email',
                'assignments.officer:id,name',
                'statusUpdates.officer:id,name',
                'attachments',
            ]),
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$this->canManageRequest($request->user(), $serviceRequest)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'priority' => 'sometimes|required|in:LOW,MEDIUM,HIGH,URGENT',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $serviceRequest->update($data);
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE_REQUEST',
            'entity' => 'ServiceRequest',
            'entity_id' => $serviceRequest->id,
        ]);

        return response()->json(['success' => true, 'data' => $serviceRequest->fresh()->load('category')]);
    }

    public function destroy(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$this->canManageRequest($request->user(), $serviceRequest)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'DELETE_REQUEST',
            'entity' => 'ServiceRequest',
            'entity_id' => $serviceRequest->id,
        ]);
        $serviceRequest->delete();

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'officer_id' => 'required|exists:users,id',
            'notes'      => 'nullable|string',
        ]);

        $officer = \App\Models\User::findOrFail($data['officer_id']);
        if (!$officer->hasRole('OFFICER')) {
            return response()->json(['message' => 'User is not an officer'], 400);
        }

        $assignment = Assignment::create([
            'request_id'  => $serviceRequest->id,
            'officer_id'  => $officer->id,
            'assigned_by' => $request->user()->id,
            'notes'       => $data['notes'] ?? null,
        ]);

        $serviceRequest->update(['status' => 'ASSIGNED']);

        Notification::create([
            'user_id' => $officer->id,
            'title'   => 'New Assignment',
            'message' => "You have been assigned request #{$serviceRequest->id}",
        ]);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'action'    => 'ASSIGN_REQUEST',
            'entity'    => 'ServiceRequest',
            'entity_id' => $serviceRequest->id,
        ]);

        return response()->json(['success' => true, 'data' => $assignment]);
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$request->user()->hasRole('ADMIN') &&
            !$serviceRequest->assignments->contains('officer_id', $request->user()->id)) {
            return response()->json(['message' => 'Only the assigned officer or an admin can update status'], 403);
        }

        $data = $request->validate([
            'status'  => 'required|in:ASSIGNED,IN_PROGRESS,COMPLETED,REJECTED,CANCELLED',
            'comment' => 'nullable|string',
        ]);

        StatusUpdate::create([
            'request_id' => $serviceRequest->id,
            'officer_id' => $request->user()->id,
            'status'     => $data['status'],
            'comment'    => $data['comment'] ?? null,
        ]);

        $serviceRequest->update(['status' => $data['status']]);

        Notification::create([
            'user_id' => $serviceRequest->requester_id,
            'title'   => 'Request Update',
            'message' => "Your request #{$serviceRequest->id} is now {$data['status']}",
        ]);

        return response()->json(['success' => true]);
    }

    private function canManageRequest($user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole('ADMIN') || $serviceRequest->requester_id === $user->id;
    }
}
