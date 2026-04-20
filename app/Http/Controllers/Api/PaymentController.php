<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * DELETE /api/master/payment-history/{id}
     * Membatalkan pembayaran
     */
    public function destroy($id)
    {
        try {
            $payment = PaymentHistory::with('transaction')->findOrFail($id);
            if ($payment->transaction->owner_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $this->paymentService->cancelPayment($id);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dibatalkan dan buku kas telah disesuaikan.'
            ]);

        } catch (Exception $e) {
            Log::error('Cancel Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
