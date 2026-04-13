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
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('tipe_transaksi', ['pemasukan', 'pengeluaran']);
            $table->string('kategori')->nullable(); // e.g., DP Penjualan, Refund, Listrik, Operasional
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->nullableMorphs('referensi'); // creates referensi_type and referensi_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};
