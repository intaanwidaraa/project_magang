<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangKeluarExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize // <-- DIUBAH
{
    /**
    * @param Collection 
    */
    public function __construct(
        public Collection $records
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
            'Tgl. Dibuat',
            'Waktu Keluar',
            'Nama Pengambil',
            'Bagian',
            'Shift',
            'Nama Barang',
            'Qty Keluar',
            'Satuan',
            'Status',
            'Keterangan',
            'Info Koreksi',
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