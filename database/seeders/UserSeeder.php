<?php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@eventhub.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'user_image' => null,
            'role_id' => 1,
            'phoneNumber' => '0700000000',
            'location' => 'Nairobi',
            'gender' => 'male',
            'dateOfBirth' => '1995-01-01',
        ]);

        // Secretary
        User::create([
            'name' => 'Secretary User',
            'email' => 'secretary@eventhub.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'user_image' => null,
            'role_id' => 2,
            'phoneNumber' => '0711111111',
            'location' => 'Nairobi',
            'gender' => 'female',
            'dateOfBirth' => '1998-05-10',
        ]);

        // Normal User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@eventhub.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'user_image' => null,
            'role_id' => 3,
            'phoneNumber' => '0722222222',
            'location' => 'Nakuru',
            'gender' => 'male',
            'dateOfBirth' => '2000-08-15',
        ]);
    }
}