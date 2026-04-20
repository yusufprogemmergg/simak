<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ================================================================
        // STEP 1: RENAME TABLES
        // ================================================================
        Schema::rename('angsuran', 'installments');
        Schema::rename('alokasi_pembayaran_fleksibel', 'flexible_payment_allocations');
        Schema::rename('fleksible_payments', 'flexible_payments');
        Schema::rename('kavling', 'plots');
        Schema::rename('payment_history', 'payment_histories');
        Schema::rename('profile_perusahaan', 'company_profiles');
        Schema::rename('project', 'projects');
        Schema::rename('sales', 'sales_staff');
        Schema::rename('sales_transactions', 'transactions');

        // ================================================================
        // STEP 2: RENAME COLUMNS — installments (dari angsuran)
        // ================================================================
        Schema::table('installments', function (Blueprint $table) {
            $table->renameColumn('penjualan_id', 'transaction_id');
            $table->renameColumn('bulan_ke', 'installment_number');
            $table->renameColumn('tanggal_jatuh_tempo', 'due_date');
            $table->renameColumn('tanggal_bayar', 'paid_date');
            $table->renameColumn('nominal', 'amount');
            $table->renameColumn('sisa_setelah_bayar', 'remaining_amount');
            $table->renameColumn('keterangan', 'notes');
        });

        // Drop anti-pattern column
        Schema::table('installments', function (Blueprint $table) {
            if (Schema::hasColumn('installments', 'pembayaran_fleksibel_ids')) {
                $table->dropColumn('pembayaran_fleksibel_ids');
            }
        });

        // ================================================================
        // STEP 3: RENAME COLUMNS — flexible_payment_allocations
        // ================================================================
        Schema::table('flexible_payment_allocations', function (Blueprint $table) {
            $table->renameColumn('fleksible_payment_id', 'flexible_payment_id');
            $table->renameColumn('angsuran_id', 'installment_id');
            $table->renameColumn('nominal_dialokasikan', 'allocated_amount');
        });

        // ================================================================
        // STEP 4: RENAME COLUMNS — flexible_payments (dari fleksible_payments)
        // ================================================================
        Schema::table('flexible_payments', function (Blueprint $table) {
            $table->renameColumn('sales_transaction_id', 'transaction_id');
            $table->renameColumn('nominal', 'amount');
            $table->renameColumn('tanggal_bayar', 'paid_date');
            $table->renameColumn('catatan', 'notes');
            $table->renameColumn('metode_bayar', 'payment_method');
            $table->renameColumn('bukti_bayar', 'payment_proof');
        });

        // ================================================================
        // STEP 5: RENAME COLUMNS — plots (dari kavling)
        // ================================================================
        Schema::table('plots', function (Blueprint $table) {
            $table->renameColumn('blok_nomor', 'plot_number');
            $table->renameColumn('luas', 'area');
            $table->renameColumn('harga_dasar', 'base_price');
        });

        // ================================================================
        // STEP 6: RENAME COLUMNS — payment_histories (dari payment_history)
        // ================================================================
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->renameColumn('sales_transaction_id', 'transaction_id');
            $table->renameColumn('tanggal', 'date');
            $table->renameColumn('keterangan', 'notes');
            $table->renameColumn('referensi_type', 'referenceable_type');
            $table->renameColumn('referensi_id', 'referenceable_id');
        });

        // ================================================================
        // STEP 7: RENAME COLUMNS — company_profiles (dari profile_perusahaan)
        // ================================================================
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->renameColumn('telepon', 'phone');
            $table->renameColumn('alamat', 'address');
            $table->renameColumn('logo', 'logo_path');
            $table->renameColumn('nama_ttd_admin', 'admin_signature_name');
            $table->renameColumn('catatan_kaki_cetakan', 'print_footer');
            $table->renameColumn('format_faktur', 'invoice_format');
            $table->renameColumn('format_kuitansi', 'receipt_format');
        });

        // ================================================================
        // STEP 8: RENAME COLUMNS — projects (dari project)
        // ================================================================
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('nama_project', 'name');
            $table->renameColumn('lokasi', 'location');
            $table->renameColumn('catatan', 'notes');
            $table->renameColumn('total_unit', 'total_units');
        });

        // ================================================================
        // STEP 9: RENAME COLUMNS — sales_staff (dari sales)
        // ================================================================
        Schema::table('sales_staff', function (Blueprint $table) {
            $table->renameColumn('unit_sales', 'total_units_sold');
        });

        // ================================================================
        // STEP 10: RENAME COLUMNS — transactions (dari sales_transactions)
        // ================================================================
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('nomor_transaksi', 'transaction_number');
            $table->renameColumn('kavling_id', 'plot_id');
            $table->renameColumn('sales_id', 'sales_staff_id');
            $table->renameColumn('metode_pembayaran', 'payment_method');
            $table->renameColumn('tanggal_booking', 'booking_date');
            $table->renameColumn('harga_dasar', 'base_price');
            $table->renameColumn('promo_diskon', 'discount_amount');
            $table->renameColumn('harga_netto', 'net_price');
            $table->renameColumn('biaya_ppjb', 'ppjb_fee');
            $table->renameColumn('biaya_shm', 'shm_fee');
            $table->renameColumn('biaya_lain', 'other_fees');
            $table->renameColumn('sudah_termasuk_unit', 'is_unit_included');
            $table->renameColumn('tenor', 'tenor_months');
            $table->renameColumn('tanggal_jatuh_tempo', 'due_day');
            $table->renameColumn('uang_muka_persen', 'down_payment_percent');
            $table->renameColumn('uang_muka_nominal', 'down_payment_amount');
            $table->renameColumn('estimasi_angsuran', 'installment_estimate');
            $table->renameColumn('catatan_transaksi', 'notes');
            $table->renameColumn('status_penjualan', 'status');
            $table->renameColumn('status_dp', 'dp_status');
            $table->renameColumn('status_kpr', 'mortgage_status');
            $table->renameColumn('tanggal_pelunasan', 'settlement_date');
            $table->renameColumn('keterangan_batal', 'cancellation_notes');
        });

        // ================================================================
        // STEP 11: RENAME COLUMNS — buyers
        // ================================================================
        Schema::table('buyers', function (Blueprint $table) {
            $table->renameColumn('no_telepon', 'phone');
            $table->renameColumn('alamat', 'address');
        });

        // ================================================================
        // STEP 12: RENAME COLUMNS — cash_flows
        // ================================================================
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->renameColumn('tanggal', 'date');
            $table->renameColumn('tipe_transaksi', 'type');
            $table->renameColumn('kategori', 'category');
            $table->renameColumn('nominal', 'amount');
            $table->renameColumn('keterangan', 'notes');
            $table->renameColumn('referensi_type', 'referenceable_type');
            $table->renameColumn('referensi_id', 'referenceable_id');
        });

        // ================================================================
        // STEP 13: RENAME COLUMNS — users
        // ================================================================
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('no_telepon', 'phone');
        });

        // ================================================================
        // STEP 14: FIX DATA TYPES — transactions (total_* cols: int → decimal)
        // ================================================================
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->change();
            $table->decimal('total_paid', 15, 2)->nullable()->default(0)->change();
            $table->decimal('total_flexible_paid', 15, 2)->nullable()->default(0)->change();
        });

        // ================================================================
        // STEP 15: FIX INCONSISTENT owner_id TYPE
        // buyers, company_profiles, projects had int instead of bigint UNSIGNED
        // ================================================================
        Schema::table('buyers', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->change();
        });
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->change();
        });

        // ================================================================
        // STEP 16: ADD MISSING INDEXES for performance
        // ================================================================
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->index('date', 'cash_flows_date_index');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('booking_date', 'transactions_booking_date_index');
        });
        Schema::table('installments', function (Blueprint $table) {
            $table->index('due_date', 'installments_due_date_index');
            $table->index(['transaction_id', 'status'], 'installments_transaction_status_index');
        });

        // ================================================================
        // STEP 17: UPDATE ENUM VALUES
        // PENTING: Update data dulu, baru MODIFY COLUMN enum ke nilai baru
        // ================================================================

        // cash_flows.type: pemasukan → income, pengeluaran → expense
        // Expand enum dulu untuk allow semua nilai, lalu update data, lalu restrict
        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('pemasukan','pengeluaran','income','expense') NOT NULL");
        DB::statement("UPDATE cash_flows SET `type` = 'income' WHERE `type` = 'pemasukan'");
        DB::statement("UPDATE cash_flows SET `type` = 'expense' WHERE `type` = 'pengeluaran'");
        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('income','expense') NOT NULL");

        // transactions.payment_method: expand → update → restrict
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('cash_keras','angsuran_in_house','kpr_bank','full_cash','installment','bank_mortgage') NULL");
        DB::statement("UPDATE transactions SET payment_method = 'full_cash' WHERE payment_method = 'cash_keras'");
        DB::statement("UPDATE transactions SET payment_method = 'installment' WHERE payment_method = 'angsuran_in_house'");
        DB::statement("UPDATE transactions SET payment_method = 'bank_mortgage' WHERE payment_method = 'kpr_bank'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('full_cash','installment','bank_mortgage') NULL");

        // transactions.status: expand → update → restrict
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancel','refund','cancelled','refunded') NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE transactions SET status = 'cancelled' WHERE status = 'cancel'");
        DB::statement("UPDATE transactions SET status = 'refunded' WHERE status = 'refund'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancelled','refunded') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Revert enum values
        DB::statement("UPDATE transactions SET status = 'cancel' WHERE status = 'cancelled'");
        DB::statement("UPDATE transactions SET status = 'refund' WHERE status = 'refunded'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancel','refund') NOT NULL DEFAULT 'active'");

        DB::statement("UPDATE transactions SET payment_method = 'cash_keras' WHERE payment_method = 'full_cash'");
        DB::statement("UPDATE transactions SET payment_method = 'angsuran_in_house' WHERE payment_method = 'installment'");
        DB::statement("UPDATE transactions SET payment_method = 'kpr_bank' WHERE payment_method = 'bank_mortgage'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('cash_keras','angsuran_in_house','kpr_bank') NULL");

        DB::statement("UPDATE cash_flows SET `type` = 'pemasukan' WHERE `type` = 'income'");
        DB::statement("UPDATE cash_flows SET `type` = 'expense' WHERE `type` = 'pengeluaran'");
        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('pemasukan','pengeluaran') NOT NULL");

        // Revert tables — reverse order
        Schema::rename('transactions', 'sales_transactions');
        Schema::rename('sales_staff', 'sales');
        Schema::rename('projects', 'project');
        Schema::rename('company_profiles', 'profile_perusahaan');
        Schema::rename('payment_histories', 'payment_history');
        Schema::rename('plots', 'kavling');
        Schema::rename('flexible_payments', 'fleksible_payments');
        Schema::rename('flexible_payment_allocations', 'alokasi_pembayaran_fleksibel');
        Schema::rename('installments', 'angsuran');
    }
};
