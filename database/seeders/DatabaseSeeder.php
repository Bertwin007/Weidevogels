<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $projectData = [
            'name' => 'Ljippelân',
            'description' => 'Greideland en weidevogels bij Ljippelân — Agrarisch Natuurfonds Fryslân.',
            'active' => true,
        ];

        if (Schema::hasColumn('projects', 'location')) {
            $projectData['location'] = 'Ljippelân, Fryslân';
        }

        Project::query()->updateOrCreate(
            ['slug' => 'ljippelan'],
            $projectData
        );

        User::query()->firstOrCreate(
            ['email' => 'admin@anf.local'],
            [
                'name' => 'ANF Beheerder',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'annotator@anf.local'],
            [
                'name' => 'Vrijwilliger Annotator',
                'password' => Hash::make('password'),
                'role' => UserRole::Annotator,
            ]
        );
    }
}
