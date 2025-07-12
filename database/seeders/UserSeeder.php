<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // สร้าง default user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0812345678',
            'password' => Hash::make('password'),
            'avatar' => null,
            'address' => 'Test Address',
            'birth_date' => '2000-01-01',
            'google_id' => null,
            'line_id' => null,
            'email_verified_at' => Carbon::now(),
            'preferences' => null,
            'status' => 'active',
            'locale' => 'th',
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        // สร้าง random users
        User::factory(19)->create();
    }
}