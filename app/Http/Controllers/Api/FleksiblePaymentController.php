<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\FleksiblePaymentService;
use Exception;
use Illuminate\Http\Request;
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
            'transaction_id' => 'required|exists:transactions,id',
            'amount'         => 'required|numeric|min:1',
            'paid_date'      => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        // Pastikan transaksi milik owner yang sedang login
        $transaction = Transaction::where('id', $validated['transaction_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        try {
            $payment = $this->paymentService->processPayment($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran fleksibel berhasil diproses dan dialokasikan otomatis.',
                'data'    => $payment
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
