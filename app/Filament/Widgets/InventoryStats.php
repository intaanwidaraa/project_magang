<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $lowStockCount = Product::where('is_consumable', true) 
                        ->whereColumn('stock', '<=', 'minimum_stock')
                        ->count();

        return [
            Stat::make('Total Jenis Produk', Product::count())
                ->description('Jumlah item unik di gudang')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Pemasok', Supplier::count())
                ->description('Jumlah supplier terdaftar')
                ->icon('heroicon-o-truck'),

            Stat::make('Item Stok Kritis', $lowStockCount)
            ->description('Produk (habis pakai) yang stoknya di bawah minimum') 
            ->icon('heroicon-o-exclamation-triangle')
            ->color($lowStockCount > 0 ? 'danger' : 'success'),
    ];
    }
}