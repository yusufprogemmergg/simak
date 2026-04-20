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
        // This migration handles the remaining column renames and enum
        // updates that were not applied in the previous failed migration.
        // Tables have already been renamed (from partial execution).
        // ================================================================

        // ----------------------------------------------------------------
        // STEP A: Rename remaining columns that may not have been applied
        // Use hasColumn checks to safely skip already-done renames
        // ----------------------------------------------------------------

        // installments
        if (Schema::hasColumn('installments', 'penjualan_id')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('penjualan_id', 'transaction_id'));
        }
        if (Schema::hasColumn('installments', 'bulan_ke')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('bulan_ke', 'installment_number'));
        }
        if (Schema::hasColumn('installments', 'tanggal_jatuh_tempo')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('tanggal_jatuh_tempo', 'due_date'));
        }
        if (Schema::hasColumn('installments', 'tanggal_bayar')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('tanggal_bayar', 'paid_date'));
        }
        if (Schema::hasColumn('installments', 'nominal')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('nominal', 'amount'));
        }
        if (Schema::hasColumn('installments', 'sisa_setelah_bayar')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('sisa_setelah_bayar', 'remaining_amount'));
        }
        if (Schema::hasColumn('installments', 'keterangan')) {
            Schema::table('installments', fn(Blueprint $t) => $t->renameColumn('keterangan', 'notes'));
        }
        if (Schema::hasColumn('installments', 'pembayaran_fleksibel_ids')) {
            Schema::table('installments', fn(Blueprint $t) => $t->dropColumn('pembayaran_fleksibel_ids'));
        }

        // flexible_payment_allocations
        if (Schema::hasColumn('flexible_payment_allocations', 'fleksible_payment_id')) {
            Schema::table('flexible_payment_allocations', fn(Blueprint $t) => $t->renameColumn('fleksible_payment_id', 'flexible_payment_id'));
        }
        if (Schema::hasColumn('flexible_payment_allocations', 'angsuran_id')) {
            Schema::table('flexible_payment_allocations', fn(Blueprint $t) => $t->renameColumn('angsuran_id', 'installment_id'));
        }
        if (Schema::hasColumn('flexible_payment_allocations', 'nominal_dialokasikan')) {
            Schema::table('flexible_payment_allocations', fn(Blueprint $t) => $t->renameColumn('nominal_dialokasikan', 'allocated_amount'));
        }

        // flexible_payments
        if (Schema::hasColumn('flexible_payments', 'sales_transaction_id')) {
            Schema::table('flexible_payments', fn(Blueprint $t) => $t->renameColumn('sales_transaction_id', 'transaction_id'));
        }
        if (Schema::hasColumn('flexible_payments', 'nominal')) {
            Schema::table('flexible_payments', fn(Blueprint $t) => $t->renameColumn('nominal', 'amount'));
        }
        if (Schema::hasColumn('flexible_payments', 'tanggal_bayar')) {
            Schema::table('flexible_payments', fn(Blueprint $t) => $t->renameColumn('tanggal_bayar', 'paid_date'));
        }
        if (Schema::hasColumn('flexible_payments', 'catatan')) {
            Schema::table('flexible_payments', fn(Blueprint $t) => $t->renameColumn('catatan', 'notes'));
        }
        if (Schema::hasColumn('flexible_payments', 'metode_bayar')) {
            Schema::table('flexible_payments', fn(Blueprint $t) => $t->renameColumn('metode_bayar', 'payment_method'));
        }
        if (Schema::hasColumn('flexible_payments', 'bukti_bayar')) {
            Schema::table('flexible_payments', fn(Blueprint $t) => $t->renameColumn('bukti_bayar', 'payment_proof'));
        }

        // plots
        if (Schema::hasColumn('plots', 'blok_nomor')) {
            Schema::table('plots', fn(Blueprint $t) => $t->renameColumn('blok_nomor', 'plot_number'));
        }
        if (Schema::hasColumn('plots', 'luas')) {
            Schema::table('plots', fn(Blueprint $t) => $t->renameColumn('luas', 'area'));
        }
        if (Schema::hasColumn('plots', 'harga_dasar')) {
            Schema::table('plots', fn(Blueprint $t) => $t->renameColumn('harga_dasar', 'base_price'));
        }

        // payment_histories
        if (Schema::hasColumn('payment_histories', 'sales_transaction_id')) {
            Schema::table('payment_histories', fn(Blueprint $t) => $t->renameColumn('sales_transaction_id', 'transaction_id'));
        }
        if (Schema::hasColumn('payment_histories', 'tanggal')) {
            Schema::table('payment_histories', fn(Blueprint $t) => $t->renameColumn('tanggal', 'date'));
        }
        if (Schema::hasColumn('payment_histories', 'keterangan')) {
            Schema::table('payment_histories', fn(Blueprint $t) => $t->renameColumn('keterangan', 'notes'));
        }
        if (Schema::hasColumn('payment_histories', 'referensi_type')) {
            Schema::table('payment_histories', fn(Blueprint $t) => $t->renameColumn('referensi_type', 'referenceable_type'));
        }
        if (Schema::hasColumn('payment_histories', 'referensi_id')) {
            Schema::table('payment_histories', fn(Blueprint $t) => $t->renameColumn('referensi_id', 'referenceable_id'));
        }

        // company_profiles
        if (Schema::hasColumn('company_profiles', 'telepon')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('telepon', 'phone'));
        }
        if (Schema::hasColumn('company_profiles', 'alamat')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('alamat', 'address'));
        }
        if (Schema::hasColumn('company_profiles', 'logo')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('logo', 'logo_path'));
        }
        if (Schema::hasColumn('company_profiles', 'nama_ttd_admin')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('nama_ttd_admin', 'admin_signature_name'));
        }
        if (Schema::hasColumn('company_profiles', 'catatan_kaki_cetakan')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('catatan_kaki_cetakan', 'print_footer'));
        }
        if (Schema::hasColumn('company_profiles', 'format_faktur')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('format_faktur', 'invoice_format'));
        }
        if (Schema::hasColumn('company_profiles', 'format_kuitansi')) {
            Schema::table('company_profiles', fn(Blueprint $t) => $t->renameColumn('format_kuitansi', 'receipt_format'));
        }

        // projects
        if (Schema::hasColumn('projects', 'nama_project')) {
            Schema::table('projects', fn(Blueprint $t) => $t->renameColumn('nama_project', 'name'));
        }
        if (Schema::hasColumn('projects', 'lokasi')) {
            Schema::table('projects', fn(Blueprint $t) => $t->renameColumn('lokasi', 'location'));
        }
        if (Schema::hasColumn('projects', 'catatan')) {
            Schema::table('projects', fn(Blueprint $t) => $t->renameColumn('catatan', 'notes'));
        }
        if (Schema::hasColumn('projects', 'total_unit')) {
            Schema::table('projects', fn(Blueprint $t) => $t->renameColumn('total_unit', 'total_units'));
        }

        // sales_staff
        if (Schema::hasColumn('sales_staff', 'unit_sales')) {
            Schema::table('sales_staff', fn(Blueprint $t) => $t->renameColumn('unit_sales', 'total_units_sold'));
        }

        // transactions
        if (Schema::hasColumn('transactions', 'nomor_transaksi')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('nomor_transaksi', 'transaction_number'));
        }
        if (Schema::hasColumn('transactions', 'kavling_id')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('kavling_id', 'plot_id'));
        }
        if (Schema::hasColumn('transactions', 'sales_id')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('sales_id', 'sales_staff_id'));
        }
        if (Schema::hasColumn('transactions', 'metode_pembayaran')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('metode_pembayaran', 'payment_method'));
        }
        if (Schema::hasColumn('transactions', 'tanggal_booking')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('tanggal_booking', 'booking_date'));
        }
        if (Schema::hasColumn('transactions', 'harga_dasar')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('harga_dasar', 'base_price'));
        }
        if (Schema::hasColumn('transactions', 'promo_diskon')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('promo_diskon', 'discount_amount'));
        }
        if (Schema::hasColumn('transactions', 'harga_netto')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('harga_netto', 'net_price'));
        }
        if (Schema::hasColumn('transactions', 'biaya_ppjb')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('biaya_ppjb', 'ppjb_fee'));
        }
        if (Schema::hasColumn('transactions', 'biaya_shm')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('biaya_shm', 'shm_fee'));
        }
        if (Schema::hasColumn('transactions', 'biaya_lain')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('biaya_lain', 'other_fees'));
        }
        if (Schema::hasColumn('transactions', 'sudah_termasuk_unit')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('sudah_termasuk_unit', 'is_unit_included'));
        }
        if (Schema::hasColumn('transactions', 'tenor')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('tenor', 'tenor_months'));
        }
        if (Schema::hasColumn('transactions', 'tanggal_jatuh_tempo')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('tanggal_jatuh_tempo', 'due_day'));
        }
        if (Schema::hasColumn('transactions', 'uang_muka_persen')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('uang_muka_persen', 'down_payment_percent'));
        }
        if (Schema::hasColumn('transactions', 'uang_muka_nominal')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('uang_muka_nominal', 'down_payment_amount'));
        }
        if (Schema::hasColumn('transactions', 'estimasi_angsuran')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('estimasi_angsuran', 'installment_estimate'));
        }
        if (Schema::hasColumn('transactions', 'catatan_transaksi')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('catatan_transaksi', 'notes'));
        }
        if (Schema::hasColumn('transactions', 'status_penjualan')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('status_penjualan', 'status'));
        }
        if (Schema::hasColumn('transactions', 'status_dp')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('status_dp', 'dp_status'));
        }
        if (Schema::hasColumn('transactions', 'status_kpr')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('status_kpr', 'mortgage_status'));
        }
        if (Schema::hasColumn('transactions', 'tanggal_pelunasan')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('tanggal_pelunasan', 'settlement_date'));
        }
        if (Schema::hasColumn('transactions', 'keterangan_batal')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->renameColumn('keterangan_batal', 'cancellation_notes'));
        }

        // buyers
        if (Schema::hasColumn('buyers', 'no_telepon')) {
            Schema::table('buyers', fn(Blueprint $t) => $t->renameColumn('no_telepon', 'phone'));
        }
        if (Schema::hasColumn('buyers', 'alamat')) {
            Schema::table('buyers', fn(Blueprint $t) => $t->renameColumn('alamat', 'address'));
        }

        // cash_flows
        if (Schema::hasColumn('cash_flows', 'tanggal')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('tanggal', 'date'));
        }
        if (Schema::hasColumn('cash_flows', 'tipe_transaksi')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('tipe_transaksi', 'type'));
        }
        if (Schema::hasColumn('cash_flows', 'kategori')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('kategori', 'category'));
        }
        if (Schema::hasColumn('cash_flows', 'nominal')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('nominal', 'amount'));
        }
        if (Schema::hasColumn('cash_flows', 'keterangan')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('keterangan', 'notes'));
        }
        if (Schema::hasColumn('cash_flows', 'referensi_type')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('referensi_type', 'referenceable_type'));
        }
        if (Schema::hasColumn('cash_flows', 'referensi_id')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->renameColumn('referensi_id', 'referenceable_id'));
        }

        // users
        if (Schema::hasColumn('users', 'no_telepon')) {
            Schema::table('users', fn(Blueprint $t) => $t->renameColumn('no_telepon', 'phone'));
        }

        // ----------------------------------------------------------------
        // STEP B: FIX DATA TYPES — transactions (int → decimal)
        // ----------------------------------------------------------------
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->change();
            $table->decimal('total_paid', 15, 2)->nullable()->default(0)->change();
            $table->decimal('total_flexible_paid', 15, 2)->nullable()->default(0)->change();
        });

        // ----------------------------------------------------------------
        // STEP C: FIX INCONSISTENT owner_id TYPE
        // ----------------------------------------------------------------
        Schema::table('buyers', fn(Blueprint $t) => $t->unsignedBigInteger('owner_id')->change());
        Schema::table('company_profiles', fn(Blueprint $t) => $t->unsignedBigInteger('owner_id')->change());
        Schema::table('projects', fn(Blueprint $t) => $t->unsignedBigInteger('owner_id')->change());

        // ----------------------------------------------------------------
        // STEP D: ADD MISSING INDEXES
        // ----------------------------------------------------------------
        if (!$this->indexExists('cash_flows', 'cash_flows_date_index')) {
            Schema::table('cash_flows', fn(Blueprint $t) => $t->index('date', 'cash_flows_date_index'));
        }
        if (!$this->indexExists('transactions', 'transactions_booking_date_index')) {
            Schema::table('transactions', fn(Blueprint $t) => $t->index('booking_date', 'transactions_booking_date_index'));
        }
        if (!$this->indexExists('installments', 'installments_due_date_index')) {
            Schema::table('installments', fn(Blueprint $t) => $t->index('due_date', 'installments_due_date_index'));
        }
        if (!$this->indexExists('installments', 'installments_transaction_status_index')) {
            Schema::table('installments', fn(Blueprint $t) => $t->index(['transaction_id', 'status'], 'installments_transaction_status_index'));
        }

        // ----------------------------------------------------------------
        // STEP E: UPDATE ENUM VALUES (expand → update data → restrict)
        // ----------------------------------------------------------------

        // cash_flows.type
        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('pemasukan','pengeluaran','income','expense') NOT NULL");
        DB::statement("UPDATE cash_flows SET `type` = 'income' WHERE `type` = 'pemasukan'");
        DB::statement("UPDATE cash_flows SET `type` = 'expense' WHERE `type` = 'pengeluaran'");
        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('income','expense') NOT NULL");

        // transactions.payment_method
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('cash_keras','angsuran_in_house','kpr_bank','full_cash','installment','bank_mortgage') NULL");
        DB::statement("UPDATE transactions SET payment_method = 'full_cash' WHERE payment_method = 'cash_keras'");
        DB::statement("UPDATE transactions SET payment_method = 'installment' WHERE payment_method = 'angsuran_in_house'");
        DB::statement("UPDATE transactions SET payment_method = 'bank_mortgage' WHERE payment_method = 'kpr_bank'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('full_cash','installment','bank_mortgage') NULL");

        // transactions.status
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancel','refund','cancelled','refunded') NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE transactions SET status = 'cancelled' WHERE status = 'cancel'");
        DB::statement("UPDATE transactions SET status = 'refunded' WHERE status = 'refund'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancelled','refunded') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Revert enums
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancel','refund','cancelled','refunded') NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE transactions SET status = 'cancel' WHERE status = 'cancelled'");
        DB::statement("UPDATE transactions SET status = 'refund' WHERE status = 'refunded'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `status` ENUM('active','paid_off','cancel','refund') NOT NULL DEFAULT 'active'");

        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('cash_keras','angsuran_in_house','kpr_bank','full_cash','installment','bank_mortgage') NULL");
        DB::statement("UPDATE transactions SET payment_method = 'cash_keras' WHERE payment_method = 'full_cash'");
        DB::statement("UPDATE transactions SET payment_method = 'angsuran_in_house' WHERE payment_method = 'installment'");
        DB::statement("UPDATE transactions SET payment_method = 'kpr_bank' WHERE payment_method = 'bank_mortgage'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN `payment_method` ENUM('cash_keras','angsuran_in_house','kpr_bank') NULL");

        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('pemasukan','pengeluaran','income','expense') NOT NULL");
        DB::statement("UPDATE cash_flows SET `type` = 'pemasukan' WHERE `type` = 'income'");
        DB::statement("UPDATE cash_flows SET `type` = 'pengeluaran' WHERE `type` = 'expense'");
        DB::statement("ALTER TABLE cash_flows MODIFY COLUMN `type` ENUM('pemasukan','pengeluaran') NOT NULL");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
