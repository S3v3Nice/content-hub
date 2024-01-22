<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Controllers\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         User::factory()->create([
             'username' => 'admin',
             'email' => 'admin@admin',
             'first_name' => 'Администратор',
             'last_name' => '',
             'role' => UserRole::Admin,
             'password' => bcrypt('admin'),
         ]);
    }
}
