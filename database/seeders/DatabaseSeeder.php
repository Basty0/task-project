<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'djaffardaoud0@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admine'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->call(TicketSeeder::class);
    }
}
