<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $completed = ServiceRequest::where('status', 'COMPLETED')->get(['created_at', 'updated_at']);
        $averageHours = $completed->isEmpty()
            ? null
            : round($completed->avg(fn ($request) => $request->created_at->diffInMinutes($request->updated_at)) / 60, 1);

        return view('requests.analytics', [
            'total' => ServiceRequest::count(),
            'open' => ServiceRequest::whereIn('status', ServiceRequest::OPEN_STATUSES)->count(),
            'overdue' => ServiceRequest::whereIn('status', ServiceRequest::OPEN_STATUSES)->where('due_at', '<', now())->count(),
            'averageHours' => $averageHours,
            'averageRating' => ServiceRequest::whereNotNull('rating')->avg('rating'),
            'ratedCount' => ServiceRequest::whereNotNull('rating')->count(),
            'byStatus' => ServiceRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'byCategory' => Category::withCount('serviceRequests')->orderByDesc('service_requests_count')->get(),
            'byLocation' => ServiceRequest::selectRaw('location, count(*) as total')
                ->groupBy('location')->orderByDesc('total')->limit(8)->get(),
        ]);
    }
}
