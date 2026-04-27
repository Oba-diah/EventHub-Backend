<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'description' => 'Full access to manage system, users, events, and bookings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'User',
                'description' => 'Can browse events and make bookings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Organizer',
                'description' => 'Can create and manage events',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}