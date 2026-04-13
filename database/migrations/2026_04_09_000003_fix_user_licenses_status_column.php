<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom status menjadi string biasa (VARCHAR)
        // agar bisa menerima nilai: 'available', 'active', 'revoked'
        // Ini menggantikan ENUM lama yang hanya punya nilai terbatas
        DB::statement("ALTER TABLE user_licenses MODIFY status VARCHAR(20) NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        // Kembalikan ke ENUM jika di-rollback
        DB::statement("ALTER TABLE user_licenses MODIFY status ENUM('pending','active','inactive','expired') NOT NULL DEFAULT 'pending'");
    }
};
