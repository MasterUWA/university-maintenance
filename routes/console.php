<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('requests:escalate', function () {
    $admins = \App\Models\User::role('ADMIN')->get();
    $overdue = \App\Models\ServiceRequest::whereIn('status', \App\Models\ServiceRequest::OPEN_STATUSES)
        ->whereNotNull('due_at')
        ->where('due_at', '<', now())
        ->whereNull('escalated_at')
        ->get();

    foreach ($overdue as $serviceRequest) {
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'Request overdue',
                'message' => "Request #{$serviceRequest->id} ({$serviceRequest->priority}) has passed its time limit.",
            ]);
        }

        $serviceRequest->forceFill(['escalated_at' => now()])->save();
    }

    $this->info("Escalated {$overdue->count()} request(s).");
})->purpose('Notify administrators about open requests that have passed their time limit');

Schedule::command('requests:escalate')->hourly();
