<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_licenses', function (Blueprint $table) {
            // user_id boleh null → agar key bisa dibuat sebelum owner mendaftar
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Kolom catatan internal (untuk siapa key ini ditujukan)
            $table->string('note')->nullable()->after('license_key');
        });
    }

    public function down(): void
    {
        Schema::table('user_licenses', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn('note');
        });
    }
};
