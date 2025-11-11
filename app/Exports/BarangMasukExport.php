<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangMasukExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
            'Tgl. Pesan',
            'Tgl. Terima',
            'No. FPPB',
            'Supplier',
            'Nama Barang',
            'Qty',
            'Satuan',
            'Harga Satuan',
            'Total Item',
            'Metode Pembayaran',
            'Status',
            'Keterangan PO',
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