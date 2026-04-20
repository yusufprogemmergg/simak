<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_history', function (Blueprint $table) {
            $table->string('referensi_type')->nullable()->after('amount');
            $table->unsignedBigInteger('referensi_id')->nullable()->after('referensi_type');
            $table->index(['referensi_type', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_history', function (Blueprint $table) {
            $table->dropIndex(['referensi_type', 'referensi_id']);
            $table->dropColumn(['referensi_type', 'referensi_id']);
        });
    }
};
