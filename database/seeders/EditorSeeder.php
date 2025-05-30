<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conference;
use App\Models\Editor;

class EditorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::where('role', 'admin')->first();
        $editor = User::where('role', 'editor')->first();
        $conferences = Conference::all();

        foreach ($conferences as $conference) {
            Editor::create([
                'user_id' => $editor->id,
                'conference_id' => $conference->id,
                'assigned_by' => $superAdmin->id,
            ]);
        }
    }
}
