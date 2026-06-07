<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Project::query()->firstOrCreate(
            ['slug' => 'ljippelan'],
            [
                'name' => 'Ljippelân',
                'description' => 'Greideland en weidevogels bij Ljippelân — Agrarisch Natuurfonds Fryslân.',
                'active' => true,
            ]
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
