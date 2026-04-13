<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('penjualan', 'sales_transactions');
    }

    public function down(): void
    {
        Schema::rename('sales_transactions', 'penjualan');
    }
};