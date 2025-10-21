<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product; // Pastikan ini ada
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $recordTitleAttribute = 'nama_item';
    protected static ?string $modelLabel = 'Barang Pemasok';
    protected static ?string $pluralModelLabel = 'Barang-barang Pemasok';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Pilih Produk dari Gudang')
                    ->relationship('product', 'name') 
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('nama_item', Product::find($state)?->name)), // Otomatis mengisi nama_item

                Forms\Components\TextInput::make('nama_item')
                    ->label('Nama Item (Versi Supplier)')
                    ->required()
                    ->maxLength(255),
                
                // Input untuk harga beli dari supplier
                 Forms\Components\TextInput::make('harga')
                    ->label('Harga Beli')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_item')
                    ->label('Nama Item')
                    ->searchable(), // <-- FUNGSI PENCARIAN DITAMBAHKAN DI SINI

                 Tables\Columns\TextColumn::make('harga')
                    ->label('Harga Beli')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->prefix('Rp ') // Menggunakan format "Rp" sesuai permintaan Anda
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}