<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'Profesor Uno',
                'email' => 'profesor1@correo.com',
            ],
            [
                'name' => 'Profesor Dos',
                'email' => 'profesor2@correo.com',
            ],
        ];

        foreach ($teachers as $teacher) {
            User::updateOrCreate(
                ['email' => $teacher['email']],
                [
                    'name' => $teacher['name'],
                    'password' => Hash::make('password'),
                    'role' => 'docente',
                ]
            );
        }
    }
}
