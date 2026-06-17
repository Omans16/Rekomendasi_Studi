<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['nisn' => 'admin'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
                'kelas' => null,
            ]
        );

        User::updateOrCreate(
            ['nisn' => 'gurubk'],
            [
                'name' => 'Guru BK',
                'password' => Hash::make('gurubk12345'),
                'role' => 'guru_bk',
                'kelas' => null,
            ]
        );
    }
}