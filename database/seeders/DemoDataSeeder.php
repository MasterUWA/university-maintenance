<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\StatusUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('ADMIN')->firstOrFail();
        $officers = collect([
            ['name' => 'Maintenance Officer Two', 'email' => 'officer2@univ.edu'],
            ['name' => 'Maintenance Officer Three', 'email' => 'officer3@univ.edu'],
        ])->map(function (array $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make('officer123')]
            );
            $user->assignRole('OFFICER');
            return $user;
        });

        $students = collect([
            ['name' => 'Student Two', 'email' => 'student2@univ.edu'],
            ['name' => 'Student Three', 'email' => 'student3@univ.edu'],
            ['name' => 'Staff Member One', 'email' => 'staff1@univ.edu'],
            ['name' => 'Staff Member Two', 'email' => 'staff2@univ.edu'],
        ])->map(function (array $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make('student123')]
            );
            $user->assignRole('STUDENT_STAFF');
            return $user;
        });

        $students = $students->prepend(User::role('STUDENT_STAFF')->where('email', 'student@univ.edu')->firstOrFail());
        $officers = $officers->prepend(User::role('OFFICER')->where('email', 'officer@univ.edu')->firstOrFail());
        $categories = Category::query()->pluck('id')->values();
        $target = 75;
        $existing = ServiceRequest::count();

        for ($number = $existing + 1; $number <= $target; $number++) {
            $requester = $students[($number - 1) % $students->count()];
            $status = ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED', 'CANCELLED'][$number % 6];
            $serviceRequest = ServiceRequest::create([
                'title' => "Campus maintenance issue #{$number}",
                'description' => "Demo report for testing the maintenance workflow. Issue reference {$number}.",
                'location' => ['Main Library', 'Science Block', 'Student Residence', 'Administration Building', 'Sports Centre'][$number % 5],
                'priority' => ['LOW', 'MEDIUM', 'HIGH', 'URGENT'][$number % 4],
                'status' => $status,
                'category_id' => $categories[($number - 1) % $categories->count()],
                'requester_id' => $requester->id,
            ]);

            if ($status !== 'PENDING') {
                $officer = $officers[($number - 1) % $officers->count()];
                Assignment::create([
                    'request_id' => $serviceRequest->id,
                    'officer_id' => $officer->id,
                    'assigned_by' => $admin->id,
                    'notes' => 'Demo assignment for workflow testing.',
                ]);
                StatusUpdate::create([
                    'request_id' => $serviceRequest->id,
                    'officer_id' => $officer->id,
                    'status' => $status,
                    'comment' => 'Demo status update for testing.',
                ]);
            }

            AuditLog::create([
                'user_id' => $requester->id,
                'action' => 'CREATE_REQUEST',
                'entity' => 'ServiceRequest',
                'entity_id' => $serviceRequest->id,
            ]);

            if ($number % 5 === 0) {
                Notification::create([
                    'user_id' => $requester->id,
                    'title' => 'Request update',
                    'message' => "Demo request #{$serviceRequest->id} is currently {$status}.",
                ]);
            }
        }

        foreach (ServiceRequest::with('assignments')->get() as $serviceRequest) {
            Notification::firstOrCreate([
                'user_id' => $admin->id,
                'title' => 'Request queue update',
                'message' => "Request #{$serviceRequest->id} is {$serviceRequest->status} and needs monitoring.",
            ]);

            foreach ($serviceRequest->assignments as $assignment) {
                Notification::firstOrCreate([
                    'user_id' => $assignment->officer_id,
                    'title' => 'Assigned maintenance request',
                    'message' => "Request #{$serviceRequest->id} is assigned to your maintenance queue.",
                ]);
            }
        }
    }
}
