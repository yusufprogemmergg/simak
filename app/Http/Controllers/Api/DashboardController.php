<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\CashFlow;
use App\Models\Transaction;
use App\Models\Plot;
use App\Models\Project;
use App\Models\Installment;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $ownerId = $user->id;
        $now     = Carbon::now();

        // FILTER PERIODE
        $filter    = $request->query('filter', 'bulan_ini');
        $startDate = null;
        $endDate   = null;

        switch ($filter) {
            case 'minggu_ini':
                $startDate = $now->copy()->startOfWeek();
                $endDate   = $now->copy()->endOfWeek();
                break;
            case 'bulan_ini':
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                break;
            case 'tahun_ini':
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
            case 'tahun_lalu':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate   = $now->copy()->subYear()->endOfYear();
                break;
        }

        // PERIODE PEMBANDING
        $bandingkan    = filter_var($request->query('bandingkan', false), FILTER_VALIDATE_BOOLEAN);
        $startDateLalu = null;
        $endDateLalu   = null;

        if ($bandingkan && $startDate && $endDate) {
            if ($filter === 'minggu_ini') {
                $startDateLalu = $startDate->copy()->subWeek();
                $endDateLalu   = $endDate->copy()->subWeek();
            } elseif ($filter === 'bulan_ini') {
                $startDateLalu = $startDate->copy()->subMonth();
                $endDateLalu   = $endDate->copy()->subMonth();
            } else {
                $startDateLalu = $startDate->copy()->subYear();
                $endDateLalu   = $endDate->copy()->subYear();
            }
        }

        $pct = function ($cur, $prev) {
            if ($prev > 0) return round((($cur - $prev) / $prev) * 100, 1);
            if ($cur > 0) return 100;
            return 0;
        };

        // PEMASUKAN
        $incomeQ = CashFlow::where('owner_id', $ownerId)
            ->where('type', 'income')
            ->when($startDate, fn($q) => $q->whereBetween('date', [$startDate, $endDate]));

        $totalIncome      = (float) $incomeQ->sum('amount');
        $countIncome      = $incomeQ->count();

        $totalIncomePrev  = $bandingkan && $startDateLalu
            ? (float) CashFlow::where('owner_id', $ownerId)
                ->where('type', 'income')
                ->whereBetween('date', [$startDateLalu, $endDateLalu])
                ->sum('amount')
            : 0;

        // DP DITERIMA
        $dpQ = CashFlow::where('owner_id', $ownerId)
            ->where('type', 'income')
            ->whereIn('category', ['DP Penjualan', 'Booking Fee'])
            ->when($startDate, fn($q) => $q->whereBetween('date', [$startDate, $endDate]));

        $totalDp      = (float) $dpQ->sum('amount');
        $countDp      = $dpQ->count();

        $totalDpPrev  = $bandingkan && $startDateLalu
            ? (float) CashFlow::where('owner_id', $ownerId)
                ->where('type', 'income')
                ->whereIn('category', ['DP Penjualan', 'Booking Fee'])
                ->whereBetween('date', [$startDateLalu, $endDateLalu])
                ->sum('amount')
            : 0;

        // PENJUALAN
        $transactionQ = Transaction::where('owner_id', $ownerId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->when($startDate, fn($q) => $q->whereBetween('booking_date', [$startDate, $endDate]));

        $totalTransactionValue = (float) $transactionQ->sum('grand_total');
        $totalTransactionCount = $transactionQ->count();

        $totalTransactionCountPrev = $bandingkan && $startDateLalu
            ? Transaction::where('owner_id', $ownerId)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->whereBetween('booking_date', [$startDateLalu, $endDateLalu])
                ->count()
            : 0;

        // PENJUALAN BATAL
        $cancelledQ = Transaction::where('owner_id', $ownerId)
            ->whereIn('status', ['cancelled', 'refunded'])
            ->when($startDate, fn($q) => $q->whereBetween('booking_date', [$startDate, $endDate]));

        $totalCancelledUnit  = $cancelledQ->count();
        $totalCancelledValue = (float) $cancelledQ->sum('grand_total');

        // PIUTANG
        $activeTransactions = Transaction::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->get();

        $totalReceivable      = $activeTransactions->sum(fn($s) => max(0, $s->grand_total - $s->total_paid));
        $countActiveTransactions = $activeTransactions->count();

        // PERSEDIAAN
        $projects         = Project::where('owner_id', $ownerId)
            ->with(['plots' => fn($q) => $q->where('status', 'available')])
            ->get();

        $persediaanNilai  = $projects->sum(fn($p) => $p->plots->sum('base_price'));
        $persediaanCount  = $projects->sum(fn($p) => $p->plots->count());

        $persediaanPerProyek = [];
        foreach ($projects as $p) {
            $unit  = $p->plots->count();
            $nilai = $p->plots->sum('base_price');
            $persediaanPerProyek[] = [
                'label'      => $p->name,
                'nilai'      => (float) $nilai,
                'unit'       => $unit,
                'persentase' => $persediaanCount > 0 ? round(($unit / $persediaanCount) * 100) : 0,
            ];
        }

        // TREN PENJUALAN
        $salesRaw = Transaction::where('owner_id', $ownerId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->when($startDate, fn($q) => $q->whereBetween('booking_date', [$startDate, $endDate]))
            ->get();

        $tren = [];

        if ($filter === 'minggu_ini') {
            for ($i = 0; $i < 7; $i++) {
                $date  = $startDate->copy()->addDays($i);
                $slice = $salesRaw->filter(fn($s) => Carbon::parse($s->booking_date)->isSameDay($date));
                $tren[] = [
                    'label' => $date->format('D'),
                    'nilai' => (float) $slice->sum('grand_total'),
                    'unit'  => $slice->count(),
                ];
            }
        } elseif ($filter === 'bulan_ini') {
            for ($i = 1; $i <= 5; $i++) {
                $start = $startDate->copy()->addDays(($i - 1) * 7);
                $end   = $start->copy()->addDays(6);
                $slice = $salesRaw->filter(fn($s) => Carbon::parse($s->booking_date)->between($start, $end));
                $tren[] = [
                    'label' => "W$i",
                    'nilai' => (float) $slice->sum('grand_total'),
                    'unit'  => $slice->count(),
                ];
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $start = Carbon::create($startDate->year, $m, 1);
                $end   = $start->copy()->endOfMonth();
                $slice = $salesRaw->filter(fn($s) => Carbon::parse($s->booking_date)->between($start, $end));
                $tren[] = [
                    'label' => "M$m",
                    'nilai' => (float) $slice->sum('grand_total'),
                    'unit'  => $slice->count(),
                ];
            }
        }

        // MARKETING
        $marketing = $salesRaw->groupBy('sales_staff_id')->map(function ($items) {
            $col = collect($items);
            return [
                'label' => optional($col->first()->salesStaff)->name ?? 'Unknown',
                'nilai' => (float) $col->sum('grand_total'),
                'unit'  => $col->count(),
            ];
        })->values();

        return response()->json([
            'summary' => [
                'penerimaan_periode_ini' => [
                    'value'           => $totalIncome,
                    'total_transaksi' => $countIncome,
                    'comparison'      => $bandingkan ? [
                        'value'      => $totalIncomePrev,
                        'percentage' => $pct($totalIncome, $totalIncomePrev)
                    ] : null
                ],
                'total_dp_diterima' => [
                    'value'          => $totalDp,
                    'total_penjualan'=> $countDp,
                    'comparison'     => $bandingkan ? [
                        'value'      => $totalDpPrev,
                        'percentage' => $pct($totalDp, $totalDpPrev)
                    ] : null
                ],
                'total_penjualan' => [
                    'unit'  => $totalTransactionCount,
                    'nilai' => $totalTransactionValue
                ],
                'total_penjualan_batal' => [
                    'unit'  => $totalCancelledUnit,
                    'nilai' => $totalCancelledValue
                ],
                'total_piutang' => [
                    'value'                   => $totalReceivable,
                    'total_penjualan_aktif'   => $countActiveTransactions
                ],
                'nilai_persediaan' => [
                    'value'      => $persediaanNilai,
                    'total_unit' => $persediaanCount,
                    'per_project'=> $persediaanPerProyek
                ]
            ],
            'tren_penjualan' => [
                'label'       => array_column($tren, 'label'),
                'nilai'       => array_column($tren, 'nilai'),
                'unit'        => array_column($tren, 'unit'),
                'proyeksi_ai' => null
            ],
            'performa_tim_marketing'  => $marketing,
            'penjualan_per_proyek'    => [],
            'persediaan_per_proyek'   => $persediaanPerProyek
        ]);
    }
}