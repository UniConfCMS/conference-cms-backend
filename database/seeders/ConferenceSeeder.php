<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conference;

class ConferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        Conference::create([
            'title' => 'Tech Summit 2023',
            'year' => 2023,
            'created_by' => $admin->id,
        ]);

        Conference::create([
            'title' => 'Innovation Conference 2024',
            'year' => 2024,
            'created_by' => $admin->id,
        ]);

        Conference::create([
            'title' => 'Global Tech Fest 2025',
            'year' => 2025,
            'created_by' => $admin->id,
        ]);
    }
}
