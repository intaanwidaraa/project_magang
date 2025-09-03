<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FrequentlyIssuedProducts extends ChartWidget
{
    protected static ?string $heading = '5 Barang Paling Sering Keluar';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = StockMovement::where('type', 'out')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Keluar',
                    'data' => $data->pluck('total_quantity')->toArray(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
