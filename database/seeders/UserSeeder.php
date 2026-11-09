<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@gsp.co.id',
                'password' => 'admin123',
            ]
        );

        $staff = [
            ['Rina Kartika', 'rina', 'warehouse'],
            ['Budi Santoso', 'budi', 'purchasing'],
            ['Siti Aminah', 'siti', 'sales'],
            ['Agus Wijaya', 'agus', 'warehouse'],
            ['Dewi Lestari', 'dewi', 'finance'],
            ['Eko Prasetyo', 'eko', 'sales'],
            ['Fitri Handayani', 'fitri', 'purchasing'],
            ['Gunawan Ali', 'gunawan', 'warehouse'],
            ['Hesti Maulana', 'hesti', 'finance'],
            ['Irfan Hakim', 'irfan', 'sales'],
            ['Joko Susilo', 'joko', 'admin_cabang'],
        ];

        foreach ($staff as $index => [$name, $username, $role]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $username.'@gsp.co.id',
                    'password' => 'password',
                ]
            );
        }
    }
}
