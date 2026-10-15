<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin',          'email' => 'admin@example.com', 'hobbies' => ['Membaca', 'Badminton']],
            ['name' => 'Budi Santoso',   'email' => 'budi@example.com',  'hobbies' => ['Memancing', 'Fotografi', 'Bersepeda']],
            ['name' => 'Sari Wulandari', 'email' => 'sari@example.com',  'hobbies' => ['Memasak']],
            ['name' => 'Andi Pratama',   'email' => 'andi@example.com',  'hobbies' => []],
        ];

        foreach ($users as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
            ]);

            $user->hobbies()->createMany(
                array_map(fn ($name) => ['name' => $name], $data['hobbies'])
            );
        }
    }
}
