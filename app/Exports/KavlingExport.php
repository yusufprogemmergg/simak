<?php

namespace App\Exports;

use App\Models\Kavling;
use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KavlingExport
{
    protected int  $ownerId;
    protected ?int $projectId;

    public function __construct(int $ownerId, ?int $projectId = null)
    {
        $this->ownerId   = $ownerId;
        $this->projectId = $projectId;
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Kavling');

        // ── Header ──────────────────────────────────────────────
        $headers = [
            'A1' => 'No',
            'B1' => 'Blok / Nomor',
            'C1' => 'Nama Project',
            'D1' => 'Luas (m²)',
            'E1' => 'Harga Dasar (Rp)',
            'F1' => 'Status',
            'G1' => 'Dibuat Pada',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF16A34A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFBBF7D0'],
                ],
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data ─────────────────────────────────────────────────
        $ownerProjectIds = Project::where('owner_id', $this->ownerId)->pluck('id');

        $query = Kavling::with('project')
            ->whereIn('project_id', $ownerProjectIds)
            ->latest();

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        $kavlings = $query->get();

        $statusLabel = [
            'available' => 'Tersedia',
            'sold'      => 'Terjual',
            'reserved'  => 'Reserved',
            'active'    => 'Aktif',
        ];

        // Warna status
        $statusColor = [
            'available' => 'FF16A34A', // hijau
            'sold'      => 'FFDC2626', // merah
            'reserved'  => 'FFD97706', // kuning
            'active'    => 'FF2563EB', // biru
        ];

        $row = 2;
        foreach ($kavlings as $no => $kavling) {
            $sheet->setCellValue("A{$row}", $no + 1);
            $sheet->setCellValue("B{$row}", $kavling->blok_nomor);
            $sheet->setCellValue("C{$row}", $kavling->project->nama_project ?? '-');
            $sheet->setCellValue("D{$row}", number_format((float) $kavling->luas, 2, ',', '.'));
            $sheet->setCellValue("E{$row}", number_format((float) $kavling->harga_dasar, 0, ',', '.'));
            $sheet->setCellValue("F{$row}", $statusLabel[$kavling->status] ?? $kavling->status);
            $sheet->setCellValue("G{$row}", $kavling->created_at?->format('d/m/Y') ?? '-');

            // Zebra stripe
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFdcfce7');
            }

            // Border tiap baris
            $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setARGB('FFD1D5DB');

            // Warna teks status
            $color = $statusColor[$kavling->status] ?? 'FF374151';
            $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB($color);
            $sheet->getStyle("F{$row}")->getFont()->setBold(true);

            // Center alignment
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // ── Auto size columns ─────────────────────────────────────
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Stream response ───────────────────────────────────────
        $filename = 'data-kavling-' . now()->format('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
