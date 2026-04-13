<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ubah kolom role menjadi string biasa agar bisa menampung nilai baru
            // Nilai valid: 'owner', 'salesman', 'super_admin'
            $table->string('role')->default('owner')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('owner')->change();
        });
    }
};
