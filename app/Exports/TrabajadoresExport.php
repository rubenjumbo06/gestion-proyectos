<?php

namespace App\Exports;

use App\Models\Trabajadores;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Log;

class TrabajadoresExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithColumnFormatting, WithMapping
{
    public function collection()
    {
        $trabajadores = Trabajadores::with('departamento')->select(
            'id_trabajadores', // Añadido para evitar null
            'nombre_trab',
            'apellido_trab',
            'dni_trab',
            'correo_trab',
            'num_telef',
            'sexo_trab',
            'fecha_nac',
            'id_departamento'
        )->get();

        // Log para depurar
        Log::info('Trabajadores exportados:', [
            'count' => $trabajadores->count(),
            'trabajadores' => $trabajadores->map(function ($t) {
                return [
                    'id_trabajadores' => $t->id_trabajadores,
                    'id_departamento' => $t->id_departamento,
                    'departamento' => $t->departamento ? $t->departamento->toArray() : null,
                ];
            })->toArray(),
        ]);

        return $trabajadores;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Apellidos',
            'DNI',
            'Correo',
            'Teléfono',
            'Sexo',
            'Fecha de Nacimiento',
            'Departamento'
        ];
    }

    public function map($row): array
    {
        return [
            $row->nombre_trab,
            $row->apellido_trab,
            $row->dni_trab,
            $row->correo_trab,
            $row->num_telef,
            $row->sexo_trab,
            $row->fecha_nac ? $row->fecha_nac->format('d/m/Y') : '',
            $row->departamento ? $row->departamento->nombre_dep : 'Sin departamento',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'c00c0c']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:H' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'alignment' => [
                'wrapText' => true,
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 15,
            'D' => 30,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 25,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => 'dd/mm/yyyy',
        ];
    }
}
