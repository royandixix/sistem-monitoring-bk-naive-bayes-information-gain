<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password1234');

        $users = [
            [
                'name' => 'Guru BK',
                'email' => 'gurubk@gmail.com',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Pengurus OSIS',
                'email' => 'osis@gmail.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepalasekolah@gmail.com',
                'role' => 'kepala_sekolah',
            ],
            [
                'name' => 'Wali Murid',
                'email' => 'walimurid@gmail.com',
                'role' => 'wali_murid',
            ],
        ];

        foreach ($users as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command?->info('4 akun demo berhasil dibuat. Password semua akun: password1234');
    }
}
