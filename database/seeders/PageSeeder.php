<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conference;
use App\Models\Page;
use Illuminate\Support\Str;


class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::whereIn('role', ['admin', 'editor'])->first();
        $conferences = Conference::all();

        if (!$user) {
            throw new \Exception('No admin or editor user found. Please run UserSeeder first.');
        }

        foreach ($conferences as $conference) {
            Page::create([
                'conference_id' => $conference->id,
                'title' => 'Welcome to ' . $conference->title,
                'slug' => Str::slug('Welcome to ' . $conference->title),
                'content' => '<p>Welcome to the ' . $conference->title . '! This is the main page content.</p>',
                'created_by' => $user->id,
            ]);

            Page::create([
                'conference_id' => $conference->id,
                'title' => 'Schedule for ' . $conference->title,
                'slug' => Str::slug('Schedule for ' . $conference->title),
                'content' => '<p>Here is the schedule for ' . $conference->title . '.</p>',
                'created_by' => $user->id,
            ]);
        }
    }
}
