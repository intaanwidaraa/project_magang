<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget

{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_consumable', true)
                    ->whereColumn('stock', '<=', 'minimum_stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Barang'),
                Tables\Columns\TextColumn::make('stock')->label('Stok Saat Ini')
                    ->badge()->color('danger'),
                Tables\Columns\TextColumn::make('minimum_stock')->label('Stok Minimum')
                    ->badge()->color('warning'),
            ])
            ->paginated(true) 
            ->defaultPaginationPageOption(10) 
            ->paginationPageOptions([5, 10, 25, 50, 'all']); 
    }

}