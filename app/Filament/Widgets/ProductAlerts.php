<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductAlerts extends BaseWidget
{
    protected static ?int $sort = 1; // Biar tampil paling atas di dashboard
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where(function ($query) {
                        $today = Carbon::today();

                        $query
                            // kondisi stok kritis
                            ->whereColumn('stock', '<=', 'minimum_stock')
                            // kondisi lifetime hampir habis (H-7)
                            ->orWhereRaw("
                                DATE_ADD(tanggal_mulai_pemakaian, INTERVAL lifetime_penggunaan DAY) <= DATE_ADD(?, INTERVAL 7 DAY)
                            ", [$today]);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn ($record) => $record->stock <= $record->minimum_stock ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('sisa_lifetime')
                    ->label('Sisa Lifetime')
                    ->state(function ($record) {
                        if (!$record->tanggal_mulai_pemakaian || !$record->lifetime_penggunaan) {
                            return null;
                        }
                        $expiredAt = Carbon::parse($record->tanggal_mulai_pemakaian)
                            ->addDays($record->lifetime_penggunaan);

                        // ✅ pakai Carbon::today() biar bulat, tanpa pecahan
                        return Carbon::today()->diffInDays($expiredAt, false);
                    })
                    ->suffix(' hari')
                    ->badge()
                    ->color(fn ($state) => $state !== null && $state <= 7 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->state(function ($record) {
                        $isStockCritical = $record->stock <= $record->minimum_stock;
                        $sisa = null;

                        if ($record->tanggal_mulai_pemakaian && $record->lifetime_penggunaan) {
                            $expiredAt = Carbon::parse($record->tanggal_mulai_pemakaian)
                                ->addDays($record->lifetime_penggunaan);
                            $sisa = now()->diffInDays($expiredAt, false);
                        }

                        $isLifetimeExpiring = $sisa !== null && $sisa <= 7;

                        if ($isStockCritical && $isLifetimeExpiring) {
                            return '🔴 Urgent';
                        } elseif ($isStockCritical) {
                            return '🟠 Warning';
                        } elseif ($isLifetimeExpiring) {
                            return '🟡 Reminder';
                        }
                        return '-';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        '🔴 Urgent' => 'danger',
                        '🟠 Warning' => 'warning',
                        '🟡 Reminder' => 'info',
                        default => 'gray',
                    }),
            ])
            ->defaultSort(function ($query) {
                return $query->orderByRaw("
                    CASE
                        WHEN stock <= minimum_stock
                         AND DATE_ADD(tanggal_mulai_pemakaian, INTERVAL lifetime_penggunaan DAY) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1
                        WHEN stock <= minimum_stock THEN 2
                        WHEN DATE_ADD(tanggal_mulai_pemakaian, INTERVAL lifetime_penggunaan DAY) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 3
                        ELSE 4
                    END
                ");
            })
            ->paginated(false);
    }
}
