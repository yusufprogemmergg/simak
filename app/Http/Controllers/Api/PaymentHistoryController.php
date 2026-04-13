<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use App\Models\SalesTransaction;
use App\Helpers\TerbilangHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function printKuitansi($id)
    {
        $payment = PaymentHistory::with([
            'salesTransaction.buyer',
            'salesTransaction.kavling.project',
            'salesTransaction.owner.profilePerusahaan'
        ])->findOrFail($id);

        // Pastikan payment history ini milik transaksi yang dimiliki owner yang login
        $transaction = SalesTransaction::where('id', $payment->sales_transaction_id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $profile = optional($transaction->owner)->profilePerusahaan;
        $buyer   = $transaction->buyer;
        $kavling = $transaction->kavling;
        $project = optional($kavling)->project;

        // Nomor Kuitansi
        $pattern    = $profile && $profile->format_kuitansi ? $profile->format_kuitansi : 'KUI/{Y}/{m}/{id}';
        $kuitansiNo = str_replace(
            ['{id}', '{Y}', '{m}', '{d}'],
            [
                str_pad($payment->id, 4, '0', STR_PAD_LEFT),
                date('Y', strtotime($payment->tanggal)),
                date('m', strtotime($payment->tanggal)),
                date('d', strtotime($payment->tanggal)),
            ],
            $pattern
        );

        // Convert amount to words
        $terbilang = TerbilangHelper::formatRupiah($payment->amount);

        // Process Logo (convert to base64)
        $logoBase64 = null;
        if ($profile && $profile->logo) {
            $path = storage_path('app/public/' . $profile->logo);
            if (file_exists($path)) {
                $type       = pathinfo($path, PATHINFO_EXTENSION);
                $data       = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $data = [
            'payment'    => $payment,
            'transaction'=> $transaction,
            'profile'    => $profile,
            'buyer'      => $buyer,
            'kavling'    => $kavling,
            'project'    => $project,
            'kuitansiNo' => $kuitansiNo,
            'terbilang'  => $terbilang,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.kuitansi', $data)->setPaper('a5', 'landscape');

        $safeFilename = str_replace(['/', '\\'], '-', $kuitansiNo);

        return $pdf->stream('Kuitansi-' . $safeFilename . '.pdf');
    }
}
