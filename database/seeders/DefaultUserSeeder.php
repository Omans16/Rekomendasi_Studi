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
                'password' => Hash::make('semegahadminrekomendasi'),
                'role' => 'admin',
                'kelas' => null,
            ]
        );
    }
}