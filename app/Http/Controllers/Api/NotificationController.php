<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function read(Request $request, int $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true, 'data' => $notification->fresh()]);
    }
}
