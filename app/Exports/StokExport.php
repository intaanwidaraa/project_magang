<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize // <-- DIUBAH
{
    /**
    * @param Collection 
    * @param string 
    */
    public function __construct(
        public Collection $records,
        public string $periode
    ) {
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->records;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'Periode',
            'Kode',
            'Nama Barang',
            'Satuan',
            'Stok Awal',
            'Masuk',
            'Keluar',
            'Stok Akhir',
        ];
    }

    /**
    * @param mixed 
    * @return array
    */
    public function map($record): array
    {
        $masuk = $record->stockMovements->where('type', 'in')->sum('quantity');
        $keluar = $record->stockMovements->where('type', 'out')->sum('quantity');
        $stokAwal = $record->stock - $masuk + $keluar;

        return [
            $this->periode,
            $record->sku,
            $record->name,
            $record->unit,
            $stokAwal,
            $masuk,
            $keluar,
            $record->stock,
        ];
    }

    /**
    * @param Worksheet 
    */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}