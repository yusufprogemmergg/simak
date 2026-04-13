<?php

namespace App\Exports;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectExport
{
    protected int $ownerId;

    public function __construct(int $ownerId)
    {
        $this->ownerId = $ownerId;
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Project');

        // ── Header ──────────────────────────────────────────────
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Project',
            'C1' => 'Lokasi',
            'D1' => 'Total Unit',
            'E1' => 'Unit Tersedia',
            'F1' => 'Unit Terjual',
            'G1' => 'Unit Reserved',
            'H1' => 'Catatan',
            'I1' => 'Dibuat Pada',
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
                'startColor' => ['argb' => 'FF2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFBFDBFE'],
                ],
            ],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data ─────────────────────────────────────────────────
        $projects = Project::with('kavling')
            ->where('owner_id', $this->ownerId)
            ->latest()
            ->get();

        $row = 2;
        foreach ($projects as $no => $project) {
            $kavling = $project->kavling;

            $sheet->setCellValue("A{$row}", $no + 1);
            $sheet->setCellValue("B{$row}", $project->nama_project);
            $sheet->setCellValue("C{$row}", $project->lokasi);
            $sheet->setCellValue("D{$row}", $project->total_unit);
            $sheet->setCellValue("E{$row}", $kavling->where('status', 'available')->count());
            $sheet->setCellValue("F{$row}", $kavling->where('status', 'sold')->count());
            $sheet->setCellValue("G{$row}", $kavling->where('status', 'reserved')->count());
            $sheet->setCellValue("H{$row}", $project->catatan ?? '-');
            $sheet->setCellValue("I{$row}", $project->created_at?->format('d/m/Y') ?? '-');

            // Zebra stripe
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:I{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDBEAFE');
            }

            // Border tiap baris
            $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setARGB('FFD1D5DB');

            // Center kolom nomor, unit
            $sheet->getStyle("A{$row}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // ── Auto size columns ─────────────────────────────────────
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Stream response ───────────────────────────────────────
        $filename = 'data-project-' . now()->format('Ymd_His') . '.xlsx';

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
