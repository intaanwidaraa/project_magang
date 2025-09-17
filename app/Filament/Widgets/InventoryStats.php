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
        // Hitung jumlah produk stok kritis
        $lowStockCount = Product::whereColumn('stock', '<=', 'minimum_stock')->count();

        // Hitung produk dengan lifetime hampir habis (contoh: lifetime_penggunaan <= 30 hari)
        $expiringLifetimeCount = Product::where('lifetime_penggunaan', '<=', 30)->count();

        return [
            Stat::make('Total Jenis Produk', Product::count())
                ->description('Jumlah item unik di gudang')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Pemasok', Supplier::count())
                ->description('Jumlah supplier terdaftar')
                ->icon('heroicon-o-truck'),

            Stat::make('Item Stok Kritis', $lowStockCount)
                ->description('Produk yang stoknya di bawah minimum')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Produk Hampir Habis Lifetime', $expiringLifetimeCount)
                ->description('Produk dengan lifetime < 30 hari')
                ->icon('heroicon-o-clock')
                ->color($expiringLifetimeCount > 0 ? 'warning' : 'success'),
        ];
    }
}
