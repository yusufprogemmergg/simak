<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat akun super_admin hanya kalau belum ada
        $existing = User::where('role', 'super_admin')->first();

        if ($existing) {
            $this->command->info('Super admin sudah ada: ' . $existing->email);
            return;
        }

        $admin = User::create([
            'username' => 'Super Admin',
            'email'    => env('SUPER_ADMIN_EMAIL', 'admin@simak.app'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'rahasia123')),
            'role'     => 'super_admin',
        ]);

        $this->command->info('Super admin berhasil dibuat!');
        $this->command->info('Email    : ' . $admin->email);
        $this->command->info('Password : ' . env('SUPER_ADMIN_PASSWORD', 'rahasia123'));
        $this->command->warn('!! Segera ganti password setelah login pertama !!');
    }
}
