<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'create request', 'view own requests', 'view all requests',
            'assign request', 'update status', 'manage users',
            'manage categories', 'export reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $adminRole   = Role::firstOrCreate(['name' => 'ADMIN']);
        $officerRole = Role::firstOrCreate(['name' => 'OFFICER']);
        $studentRole = Role::firstOrCreate(['name' => 'STUDENT_STAFF']);

        $adminRole->syncPermissions(Permission::all());
        $officerRole->syncPermissions([
            'view all requests', 'update status',
        ]);
        $studentRole->syncPermissions([
            'create request', 'view own requests',
        ]);

        // Seed users
        $admin = User::firstOrCreate(
            ['email' => 'admin@univ.edu'],
            ['name' => 'Admin', 'password' => Hash::make('admin123')]
        );
        $admin->assignRole($adminRole);

        $officer = User::firstOrCreate(
            ['email' => 'officer@univ.edu'],
            ['name' => 'Officer One', 'password' => Hash::make('officer123')]
        );
        $officer->assignRole($officerRole);

        $student = User::firstOrCreate(
            ['email' => 'student@univ.edu'],
            ['name' => 'Student One', 'password' => Hash::make('student123')]
        );
        $student->assignRole($studentRole);

        // Seed categories
        $categories = [
            'Electrical', 'Plumbing', 'Furniture',
            'Internet/Network', 'Classroom Equipment', 'Hostel Maintenance',
        ];

        foreach ($categories as $name) {
            \App\Models\Category::firstOrCreate(['name' => $name]);
        }
    }
}
