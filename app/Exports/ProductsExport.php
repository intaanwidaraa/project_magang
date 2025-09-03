<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Mengambil data yang ingin diekspor
        return Product::select('name', 'sku', 'stock', 'minimum_stock', 'unit')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Mendefinisikan judul kolom di file Excel
        return [
            'Nama Produk',
            'SKU',
            'Stok Saat Ini',
            'Stok Minimum',
            'Satuan',
        ];
    }
}
