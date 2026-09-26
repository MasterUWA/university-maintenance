<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user:id,name,email')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->input('action')))
            ->latest()
            ->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'page' => $logs->currentPage(),
                'limit' => $logs->perPage(),
                'total' => $logs->total(),
                'pages' => $logs->lastPage(),
            ],
        ]);
    }
}
