<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! env('ADMIN_USERNAME') || ! env('ADMIN_PASSWORD')) {
            throw new \RuntimeException(
                'Set ADMIN_USERNAME and ADMIN_PASSWORD in environment before seeding.',
            );
        }
        if (Admin::where('nama_pengguna', env('ADMIN_USERNAME'))->exists()) {
            return;
        }
        if (strlen((string) env('ADMIN_PASSWORD')) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD must contain at least 12 characters.');
        }
        Admin::firstOrCreate(
            ['nama_pengguna' => env('ADMIN_USERNAME')],
            [
                'nama_admin' => env('ADMIN_NAME', 'Petugas Akademik'),
                'kata_sandi_hash' => Hash::make(env('ADMIN_PASSWORD')),
            ],
        );
    }
}
