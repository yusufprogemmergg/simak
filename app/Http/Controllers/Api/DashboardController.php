<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SalesTransaction;
use App\Models\FleksiblePayment;
use App\Models\Kavling;
use App\Models\Project;
use App\Models\Angsuran;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
             return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        $ownerId = $user->id; 

        $filter = $request->query('filter', 'bulan_ini');
        $startDate = null;
        $endDate = null;
        $now = Carbon::now();

        switch ($filter) {
            case 'minggu_ini':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'bulan_ini':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'tahun_ini':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'tahun_lalu':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate = $now->copy()->subYear()->endOfYear();
                break;
            case 'custom':
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                } else {
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                }
                break;
            case 'semua':
                break;
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // Setup logic untuk Bandingkan
        $bandingkan = filter_var($request->query('bandingkan', false), FILTER_VALIDATE_BOOLEAN);
        $startDateLalu = null;
        $endDateLalu = null;

        if ($bandingkan && $startDate && $endDate) {
            switch ($filter) {
                case 'minggu_ini':
                    $startDateLalu = $startDate->copy()->subWeek();
                    $endDateLalu = $endDate->copy()->subWeek();
                    break;
                case 'bulan_ini':
                    $startDateLalu = $startDate->copy()->subMonth();
                    $endDateLalu = $endDate->copy()->subMonth();
                    break;
                case 'tahun_ini':
                case 'tahun_lalu':
                    $startDateLalu = $startDate->copy()->subYear();
                    $endDateLalu = $endDate->copy()->subYear();
                    break;
                case 'custom':
                    $diffInDays = $startDate->diffInDays($endDate) + 1;
                    $startDateLalu = $startDate->copy()->subDays($diffInDays);
                    $endDateLalu = $endDate->copy()->subDays($diffInDays);
                    break;
            }
        }

        /**
         * Helper Function for Percentage
         */
        $calculatePercentage = function($current, $previous) {
            if ($previous > 0) {
                $percent = (($current - $previous) / $previous) * 100;
                return round($percent, 1);
            }
            if ($current > 0 && $previous == 0) {
                return 100.0;
            }
            return 0.0;
        };

        // 1. Penerimaan Periode Ini (Flexible Payments in this period)
        $penerimaanQuery = FleksiblePayment::whereHas('salesTransaction', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        });
        if ($startDate && $endDate) {
            $penerimaanQuery->whereBetween('tanggal_bayar', [$startDate, $endDate]);
        }
        $penerimaanAmount = $penerimaanQuery->sum('nominal');
        $penerimaanCount = $penerimaanQuery->count();

        $penerimaanAmountLalu = 0;
        if ($bandingkan && $startDateLalu && $endDateLalu) {
            $penerimaanAmountLalu = FleksiblePayment::whereHas('salesTransaction', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })->whereBetween('tanggal_bayar', [$startDateLalu, $endDateLalu])->sum('nominal');
        }

        // 2. Total DP Diterima
        $dpQuery = SalesTransaction::where('owner_id', $ownerId)->whereNotNull('uang_muka_nominal')->where('uang_muka_nominal', '>', 0);
        if ($startDate && $endDate) {
            $dpQuery->whereBetween('tanggal_booking', [$startDate, $endDate]);
        }
        $dpAmount = $dpQuery->sum('uang_muka_nominal');
        $dpCount = $dpQuery->count();

        $dpAmountLalu = 0;
        if ($bandingkan && $startDateLalu && $endDateLalu) {
            $dpAmountLalu = SalesTransaction::where('owner_id', $ownerId)
                ->whereNotNull('uang_muka_nominal')
                ->where('uang_muka_nominal', '>', 0)
                ->whereBetween('tanggal_booking', [$startDateLalu, $endDateLalu])
                ->sum('uang_muka_nominal');
        }

        // 3. Total Penjualan
        $penjualanQuery = SalesTransaction::where('owner_id', $ownerId)
                            ->where('status_penjualan', '!=', 'canceled');
        if ($startDate && $endDate) {
            $penjualanQuery->whereBetween('tanggal_booking', [$startDate, $endDate]);
        }
        $penjualanAmount = $penjualanQuery->sum('grand_total');
        $penjualanCount = $penjualanQuery->count();

        $penjualanCountLalu = 0;
        if ($bandingkan && $startDateLalu && $endDateLalu) {
            $penjualanCountLalu = SalesTransaction::where('owner_id', $ownerId)
                ->where('status_penjualan', '!=', 'canceled')
                ->whereBetween('tanggal_booking', [$startDateLalu, $endDateLalu])
                ->count();
        }

        // 4. Total Penjualan Batal
        $batalQuery = SalesTransaction::where('owner_id', $ownerId)
                        ->where('status_penjualan', 'canceled');
        if ($startDate && $endDate) {
            $batalQuery->whereBetween('tanggal_booking', [$startDate, $endDate]);
        }
        $batalCount = $batalQuery->count();

        // 5. Total Piutang (Global)
        $activeSales = SalesTransaction::where('owner_id', $ownerId)
                            ->where('status_penjualan', 'active')
                            ->get();
        $piutangTotal = $activeSales->sum(function ($sale) {
            return max(0, $sale->grand_total - $sale->total_paid);
        });
        $piutangCount = $activeSales->count();

        $allActiveAngsuranQuery = Angsuran::whereHas('penjualan', function($q) use ($ownerId) {
            $q->where('owner_id', $ownerId)->where('status_penjualan', 'active');
        })->whereIn('status', ['unpaid', 'terlambat', 'partial']);

        $totalActiveAngsuran = $allActiveAngsuranQuery->count();
        $tunggakanCount = 0;
        $perhatianCount = 0;
        $amanCount = 0;

        if ($totalActiveAngsuran > 0) {
           $tunggakanCount = Angsuran::whereHas('penjualan', function($q) use ($ownerId) {
                $q->where('owner_id', $ownerId)->where('status_penjualan', 'active');
           })->where(function($q) {
                $q->where('status', 'terlambat')
                  ->orWhere(function($sq) {
                      $sq->whereIn('status', ['unpaid', 'partial'])->where('tanggal_jatuh_tempo', '<', now()->toDateString());
                  });
           })->count();

           $perhatianCount = Angsuran::whereHas('penjualan', function($q) use ($ownerId) {
                $q->where('owner_id', $ownerId)->where('status_penjualan', 'active');
           })->whereIn('status', ['unpaid', 'partial'])
             ->whereBetween('tanggal_jatuh_tempo', [now()->toDateString(), now()->addDays(7)->toDateString()])
             ->count();

           $amanCount = $totalActiveAngsuran - $tunggakanCount - $perhatianCount;
           if ($amanCount < 0) $amanCount = 0;
        }

        $persenTunggakan = $totalActiveAngsuran > 0 ? round(($tunggakanCount / $totalActiveAngsuran) * 100) : 0;
        $persenPerhatian = $totalActiveAngsuran > 0 ? round(($perhatianCount / $totalActiveAngsuran) * 100) : 0;
        $persenAman = $totalActiveAngsuran > 0 ? round(($amanCount / $totalActiveAngsuran) * 100) : 0;

        // 6. Persediaan Kavling (Global)
        $persediaanKavlingQuery = Kavling::whereHas('project', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->where('status', 'available');
        $persediaanAmount = $persediaanKavlingQuery->sum('harga_dasar');
        $persediaanCount = $persediaanKavlingQuery->count();

        // CHARTS DATA
        $trenPenjualanRaw = SalesTransaction::where('owner_id', $ownerId)
            ->where('status_penjualan', '!=', 'canceled')
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('tanggal_booking', [$startDate, $endDate]);
            })
            ->get();

        // A. Tren Penjualan
        $trenData = [];
        if ($filter === 'minggu_ini') {
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $label = $date->translatedFormat('D'); 
                $salesOnDay = $trenPenjualanRaw->filter(function ($s) use ($date) {
                    return Carbon::parse($s->tanggal_booking)->isSameDay($date);
                });
                $trenData[] = [
                    'label' => $label,
                    'nilai' => $salesOnDay->sum('grand_total'),
                    'unit' => $salesOnDay->count()
                ];
            }
        } elseif ($filter === 'bulan_ini' || ($filter === 'custom' && $startDate->diffInDays($endDate) <= 31)) {
            $weeksInMonth = $startDate->diffInWeeks($endDate) + 1;
            if ($weeksInMonth > 5) $weeksInMonth = 5;
            if ($weeksInMonth <= 0) $weeksInMonth = 4;
            
            for ($i = 1; $i <= $weeksInMonth; $i++) {
                $weekStart = $startDate->copy()->addDays(($i - 1) * 7);
                $weekEnd = $i == $weeksInMonth ? $endDate->copy() : $weekStart->copy()->addDays(6);
                
                $salesInWeek = $trenPenjualanRaw->filter(function ($s) use ($weekStart, $weekEnd) {
                    $d = Carbon::parse($s->tanggal_booking);
                    return $d->between($weekStart, $weekEnd);
                });
                $trenData[] = [
                    'label' => "W$i",
                    'nilai' => $salesInWeek->sum('grand_total'),
                    'unit' => $salesInWeek->count()
                ];
            }
        } else {
            $yearToUse = $startDate ? $startDate->year : now()->year;

            if ($filter === 'custom' || $filter === 'semua') {
               $groupedByMonth = $trenPenjualanRaw->groupBy(function($item) {
                   return Carbon::parse($item->tanggal_booking)->format('Y-m');
               });
               foreach ($groupedByMonth as $monthYear => $items) {
                   $col = collect($items);
                   $trenData[] = [
                       'label' => Carbon::createFromFormat('Y-m', $monthYear)->translatedFormat('M Y'),
                       'nilai' => $col->sum('grand_total'),
                       'unit' => $col->count()
                   ];
               }
            } else {
                for ($i = 1; $i <= 12; $i++) {
                    $monthStart = Carbon::create($yearToUse, $i, 1)->startOfMonth();
                    $monthEnd = $monthStart->copy()->endOfMonth();
                    
                    $label = $monthStart->translatedFormat('M');
                    $salesInMonth = $trenPenjualanRaw->filter(function ($s) use ($monthStart, $monthEnd) {
                        $d = Carbon::parse($s->tanggal_booking);
                        return $d->between($monthStart, $monthEnd);
                    });
                    $trenData[] = [
                        'label' => $label,
                        'nilai' => $salesInMonth->sum('grand_total'),
                        'unit' => $salesInMonth->count()
                    ];
                }
            }
        }

        // B. Performa Tim Marketing
        $salesPerformance = [];
        $salesGroups = $trenPenjualanRaw->groupBy('sales_id');
        foreach ($salesGroups as $salesId => $transactions) {
            $col = collect($transactions);
            $userSales = \App\Models\User::find($salesId);
            $salesPerformance[] = [
                'label' => $userSales ? $userSales->name : 'Unknown User',
                'nilai' => $col->sum('grand_total'),
                'unit' => $col->count()
            ];
        }
        usort($salesPerformance, function($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });

        // C. Penjualan per Proyek
        $projectSalesRaw = SalesTransaction::with('kavling.project')
            ->where('owner_id', $ownerId)
            ->where('status_penjualan', '!=', 'canceled')
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('tanggal_booking', [$startDate, $endDate]);
            })
            ->get();
            
        $totalAllProjectSales = $projectSalesRaw->count();
        $projectSalesGroups = $projectSalesRaw->groupBy(function($sale) {
            return $sale->kavling && $sale->kavling->project ? $sale->kavling->project->nama_project : 'Tidak Ada Proyek';
        });

        $penjualanPerProyek = [];
        foreach ($projectSalesGroups as $projectName => $transactions) {
            $col = collect($transactions);
            $count = $col->count();
            $penjualanPerProyek[] = [
                'label' => $projectName,
                'nilai' => $col->sum('grand_total'),
                'unit' => $count,
                'persentase' => $totalAllProjectSales > 0 ? round(($count / $totalAllProjectSales) * 100) : 0
            ];
        }

        // D. Persediaan per Proyek
        $persediaanPerProyek = [];
        $projects = Project::where('owner_id', $ownerId)->with(['kavling' => function($q) {
            $q->where('status', 'available');
        }])->get();

        $totalAllKavlingTersedia = 0;
        foreach($projects as $p) {
            $totalAllKavlingTersedia += $p->kavling->count();
        }

        foreach ($projects as $project) {
            $count = $project->kavling->count();
            if ($count > 0 || true) {
                $persediaanPerProyek[] = [
                    'label' => $project->nama_project,
                    'nilai' => $project->kavling->sum('harga_dasar'),
                    'unit' => $count,
                    'persentase' => $totalAllKavlingTersedia > 0 ? round(($count / $totalAllKavlingTersedia) * 100) : 0
                ];
            }
        }

        $responseData = [
            'summary' => [
                'penerimaan_periode_ini' => [
                    'total_amount' => $penerimaanAmount,
                    'count_transaksi' => $penerimaanCount,
                ],
                'total_dp_diterima' => [
                    'total_amount' => $dpAmount,
                    'count_penjualan' => $dpCount,
                ],
                'total_penjualan' => [
                    'total_amount' => $penjualanAmount,
                    'count_unit' => $penjualanCount,
                ],
                'total_penjualan_batal' => [
                    'count_unit' => $batalCount,
                ]
            ],
            'global' => [
                'total_piutang' => [
                    'total_amount' => $piutangTotal,
                    'count_penjualan_aktif' => $piutangCount,
                    'persentase' => [
                        'tunggakan' => $persenTunggakan,
                        'perhatian' => $persenPerhatian,
                        'aman' => $persenAman,
                    ]
                ],
                'persediaan_kavling' => [
                    'total_amount' => $persediaanAmount,
                    'count_unit' => $persediaanCount,
                ]
            ],
            'charts' => [
                'tren_penjualan' => array_values($trenData),
                'performa_tim_marketing' => array_values($salesPerformance),
                'penjualan_per_proyek' => array_values($penjualanPerProyek),
                'persediaan_per_proyek' => array_values($persediaanPerProyek),
            ]
        ];

        // Apply Comparison Data
        if ($bandingkan) {
            $responseData['summary']['penerimaan_periode_ini']['comparison'] = [
                'value_lalu' => $penerimaanAmountLalu,
                'percentage' => $calculatePercentage($penerimaanAmount, $penerimaanAmountLalu),
            ];
            
            $responseData['summary']['total_dp_diterima']['comparison'] = [
                'value_lalu' => $dpAmountLalu,
                'percentage' => $calculatePercentage($dpAmount, $dpAmountLalu),
            ];

            $responseData['summary']['total_penjualan']['comparison'] = [
                'value_lalu' => $penjualanCountLalu,
                'percentage' => $calculatePercentage($penjualanCount, $penjualanCountLalu),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $responseData
        ]);
    }
}
