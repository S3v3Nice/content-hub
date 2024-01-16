<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
             'email' => 'admin@admin',
             'first_name' => 'Администратор',
             'last_name' => '',
             'is_admin' => true,
             'password' => bcrypt('admin'),
         ]);
    }
}
