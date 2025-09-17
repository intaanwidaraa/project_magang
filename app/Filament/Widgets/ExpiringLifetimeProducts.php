<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringLifetimeProducts extends BaseWidget
{
    protected static ?int $sort = 3; // urutan tampil di dashboard
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereNotNull('lifetime_penggunaan')
                    ->where('lifetime_penggunaan', '>', 0)
                    ->whereNotNull('tanggal_mulai_pemakaian') // ✅ pakai tanggal_mulai_pemakaian
                    ->where(function ($query) {
                        $today = Carbon::today();
                        $query->whereRaw("
                            DATE_ADD(tanggal_mulai_pemakaian, INTERVAL lifetime_penggunaan DAY) <= DATE_ADD(?, INTERVAL 7 DAY)
                        ", [$today]);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk'),

                Tables\Columns\TextColumn::make('lifetime_penggunaan')
                    ->label('Lifetime (hari)')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('sisa_lifetime')
                    ->label('Sisa Lifetime')
                    ->state(function ($record) {
                        if (!$record->tanggal_mulai_pemakaian) {
                            return null;
                        }
                        $expiredAt = Carbon::parse($record->tanggal_mulai_pemakaian)
                            ->addDays($record->lifetime_penggunaan);
                        return now()->diffInDays($expiredAt, false); // bisa negatif kalau sudah habis
                    })
                    ->suffix(' hari')
                    ->color(fn ($state) => $state <= 7 ? 'danger' : ($state <= 30 ? 'warning' : 'success'))
                    ->badge(),

                Tables\Columns\TextColumn::make('tanggal_mulai_pemakaian')
                    ->label('Tanggal Mulai Pakai')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Perkiraan Habis')
                    ->state(function ($record) {
                        return $record->tanggal_mulai_pemakaian
                            ? Carbon::parse($record->tanggal_mulai_pemakaian)
                                ->addDays($record->lifetime_penggunaan)
                                ->format('d M Y')
                            : '-';
                    })
                    ->color('danger')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
