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
        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            // Foreign key untuk mengaitkan dengan transaksi penjualan
            $table->foreign('sales_transaction_id')
                  ->references('id')
                  ->on('sales_transactions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembayaran');
    }
};
