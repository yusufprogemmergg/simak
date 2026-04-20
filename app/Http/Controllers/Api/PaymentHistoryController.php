<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use App\Models\Transaction;
use App\Helpers\TerbilangHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function printKuitansi($id)
    {
        $payment = PaymentHistory::with([
            'transaction.buyer',
            'transaction.plot.project',
            'transaction.owner.companyProfile'
        ])->findOrFail($id);

        // Pastikan payment history ini milik transaksi yang dimiliki owner yang login
        $transaction = Transaction::where('id', $payment->transaction_id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $profile = optional($transaction->owner)->companyProfile;
        $buyer   = $transaction->buyer;
        $plot    = $transaction->plot;
        $project = optional($plot)->project;

        // Nomor Kuitansi
        $pattern    = $profile && $profile->receipt_format ? $profile->receipt_format : 'KUI/{Y}/{m}/{id}';
        $kuitansiNo = str_replace(
            ['{id}', '{Y}', '{m}', '{d}'],
            [
                str_pad($payment->id, 4, '0', STR_PAD_LEFT),
                date('Y', strtotime($payment->date)),
                date('m', strtotime($payment->date)),
                date('d', strtotime($payment->date)),
            ],
            $pattern
        );

        $terbilang = TerbilangHelper::formatRupiah($payment->amount);

        // Logo base64
        $logoBase64 = null;
        if ($profile && $profile->logo_path) {
            $path = storage_path('app/public/' . $profile->logo_path);
            if (file_exists($path)) {
                $type       = pathinfo($path, PATHINFO_EXTENSION);
                $data       = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $data = [
            'payment'    => $payment,
            'transaction' => $transaction,
            'profile'    => $profile,
            'buyer'      => $buyer,
            'plot'       => $plot,
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
