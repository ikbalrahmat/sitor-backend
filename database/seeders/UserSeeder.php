<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@sitor.com',
            'password' => 'Admin123!', // Cukup teks biasa, Model akan otomatis men-hash
            'jabatan' => 'Kepala SPI',
            'instansi' => 'Kantor Pusat',
            'unit_kerja' => 'Satuan Pengawasan Intern',
            'role' => 'Super Admin',
            'status_keaktifan' => true,
            // Bypass security check untuk Super Admin perdana
            'is_first_login' => false,
            'password_changed_at' => Carbon::now(), // Dianggap baru ganti password hari ini
            'failed_login_attempts' => 0,
            'is_locked' => false,
        ]);
    }
}