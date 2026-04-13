<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesTransaction;
use App\Services\FleksiblePaymentService;
use Exception;
use Illuminate\Support\Facades\Log;

class FleksiblePaymentController extends Controller
{
    protected $paymentService;

    public function __construct(FleksiblePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Menyimpan dan memproses Flexible Payment baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_transaction_id' => 'required|exists:sales_transactions,id',
            'nominal'              => 'required|numeric|min:1',
            'tanggal_bayar'        => 'required|date',
            'catatan'              => 'nullable|string',
        ]);

        // Pastikan transaksi milik owner yang sedang login
        $transaction = SalesTransaction::where('id', $validated['sales_transaction_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        try {
            $pembayaran = $this->paymentService->processPayment($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran fleksibel berhasil diproses dan dialokasikan otomatis.',
                'data'    => $pembayaran
            ], 201);

        } catch (Exception $e) {
            Log::error('Flexible Payment Error: ' . $e->getMessage());

            if (strpos($e->getMessage(), 'Nominal pembayaran tidak boleh lebih') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran fleksibel.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
