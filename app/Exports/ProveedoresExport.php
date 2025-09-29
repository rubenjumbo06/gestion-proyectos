<?php

namespace App\Exports;

use App\Models\Proveedor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProveedoresExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithColumnFormatting, WithMapping
{
    public function collection()
    {
        return Proveedor::select(
            'nombre_prov',
            'descripcion_prov'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripción'
        ];
    }

    public function map($row): array
    {
        return [
            $row->nombre_prov,
            $row->descripcion_prov ?? '', // Maneja valores nulos en descripción
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilo a la cabecera
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'c00c0c']], // Color azul
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Aplicar centrado horizontal y vertical a la columna "Nombre" (A)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:A' . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Aplicar ajuste de texto (wrap text) y bordes a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:B' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'alignment' => [
                'wrapText' => true, // Permite que el texto se divida en varias líneas
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Ancho para Nombre
            'B' => 100, // Ancho para Descripción
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }
}