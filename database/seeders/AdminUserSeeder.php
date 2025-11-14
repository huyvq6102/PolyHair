<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin role exists, if not create it
        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrator role with full access']
        );

        // Check if admin user already exists
        $admin = User::where('email', 'admin@example.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'phone' => '0123456789',
                'status' => 'Hoạt động',
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Password: admin123');
        } else {
            // Update existing admin user if needed
            if (!$admin->role_id) {
                $admin->update(['role_id' => $adminRole->id]);
            }
            $this->command->warn('Admin user already exists!');
            $this->command->info('Email: ' . $admin->email);
        }
    }
}

