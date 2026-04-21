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
        Schema::table('transactions', function (Blueprint $table) {
            // Drop constraint lama yang masih mengarah ke tabel users
            $table->dropForeign('penjualan_sales_id_foreign');
            
            // Tambahkan constraint baru yang mengarah ke tabel sales_staff
            $table->foreign('sales_staff_id', 'transactions_sales_staff_id_foreign')
                  ->references('id')
                  ->on('sales_staff')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_sales_staff_id_foreign');
            
            $table->foreign('sales_staff_id', 'penjualan_sales_id_foreign')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }
};
