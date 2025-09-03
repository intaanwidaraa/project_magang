<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    // Properti untuk mengubah nama di sidebar (Bahasa Indonesia)
    protected static ?string $navigationLabel = 'Produk (Barang)';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU (Kode Barang)')
                    ->unique(ignoreRecord: true)
                    ->readOnly() // <-- Buat jadi tidak bisa diisi
                    ->placeholder('Akan dibuat otomatis setelah disimpan') // <-- Tampilkan placeholder
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->required()
                    ->default(5)
                    ->helperText('Sistem akan memberi notifikasi jika stok mencapai angka ini.'),
                Forms\Components\TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->maxLength(255)
                    ->default('pcs'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Saat Ini')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_stock')->label('Min. Stok')->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
