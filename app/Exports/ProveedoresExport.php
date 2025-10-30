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
            'tipo_identificacion',
            'identificacion',
            'nombre_prov',
            'descripcion_prov'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Tipo ID',
            'N° Identificación',
            'Nombre',
            'Descripción'
        ];
    }

    public function map($row): array
    {
        return [
            $row->tipo_identificacion ?? '-',
            $row->identificacion ?? '-',
            $row->nombre_prov,
            $row->descripcion_prov ?? 'Sin descripción',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo cabecera
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'c00c0c']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Centrado y bordes para toda la tabla
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:D' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Tipo ID
            'B' => 18,  // N° Identificación
            'C' => 35,  // Nombre
            'D' => 60,  // Descripción
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }
}