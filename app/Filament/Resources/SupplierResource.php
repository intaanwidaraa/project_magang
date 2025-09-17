<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use App\Models\Product; // <-- TAMBAHKAN USE INI
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Pemasok (Supplier)';
    protected static ?string $modelLabel = 'Pemasok';
    protected static ?string $pluralModelLabel = 'Pemasok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Pemasok')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),

                // --- MULAI PERUBAHAN PENTING ---
                Forms\Components\Repeater::make('items')
                    ->label('Barang yang Dipasok')
                    ->relationship()
                    ->schema([
                        // 1. Pilih Produk dari Master Data
                        Forms\Components\Select::make('product_id')
                            ->label('Pilih Produk dari Gudang')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive() // Membuat field ini reaktif
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Jika produk dipilih, isi otomatis nama & harga
                                $product = Product::find($state);
                                if ($product) {
                                    $set('nama_item', $product->name);
                                    // Anda bisa isi harga default dari master produk jika ada
                                    // $set('harga', $product->price); 
                                }
                            }),
                        
                        // 2. Nama Item (bisa diedit jika nama dari supplier beda)
                        Forms\Components\TextInput::make('nama_item')
                            ->label('Nama Item (Versi Supplier)')
                            ->helperText('Otomatis terisi, namun bisa diubah jika perlu.')
                            ->required(),

                        // 3. Harga dari Supplier
                        Forms\Components\TextInput::make('harga')
                            ->label('Harga Beli')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->columns(3) // Mengatur layout jadi 3 kolom
                    ->columnSpanFull()
                    ->addActionLabel('Tambah Barang Pemasok'),
                // --- SELESAI PERUBAHAN PENTING ---
            ]);
    }

    // Bagian `table()`, `getRelations()`, dan `getPages()` tidak perlu diubah
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pemasok')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Nomor Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jumlah Barang'),
                
                // ... sisa kolom ...
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}