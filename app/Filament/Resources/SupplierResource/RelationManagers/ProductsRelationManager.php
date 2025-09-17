<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Produk')
                ->relationship('suppliers', 'name')
                ->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Produk'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(), // pilih produk untuk ditambahkan ke supplier
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // hapus produk dari supplier
            ]);
    }
}
