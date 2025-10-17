<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use App\Models\Product; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\SupplierResource\RelationManagers\ItemsRelationManager; 

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
                    ->prefix('+62') // Tambahkan prefix untuk memandu user
                    ->numeric()     // Pastikan hanya angka yang dimasukkan
                    ->helperText('Masukkan nomor tanpa angka 0 di depan. Contoh: 81234567890')
                    ->maxLength(15), // Batasi panjang nomor agar lebih valid

                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('items')
                    ->label('Barang yang Dipasok')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Pilih Produk dari Gudang')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive() 
                            ->required()
                            ->hidden(fn (string $operation): bool => $operation === 'view')
                            ->afterStateUpdated(function ($state, callable $set) {
    
                                $product = Product::find($state);
                                if ($product) {
                                    $set('nama_item', $product->name);

                                }
                            })

                            // --- PENAMBAHAN FITUR DIMULAI DI SINI ---
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Produk Baru')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan (pcs, box, dll)')
                                    ->required()
                                    ->default('pcs')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('minimum_stock')
                                    ->label('Stok Minimum')
                                    ->numeric()
                                    ->default(5)
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $newProduct = Product::create($data);
                                return $newProduct->id;
                            }),
                            // --- PENAMBAHAN FITUR SELESAI ---

                        Forms\Components\TextInput::make('nama_item')
                            ->label('Nama Item (Versi Supplier)')
                            ->helperText(fn (string $operation): ?string => $operation !== 'view' ? 'Otomatis terisi, namun bisa diubah jika perlu.' : null)
                            ->required(),

                        Forms\Components\TextInput::make('harga')
                            ->label('Harga Beli')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->columns(3) 
                    ->columnSpanFull()
                    ->addActionLabel('Tambah Barang Pemasok')
                    ->visibleOn('create'),
            ]);
    }

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
                
            ])
            ->filters([
                
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
            ItemsRelationManager::class, // <-- Daftarkan di sini
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