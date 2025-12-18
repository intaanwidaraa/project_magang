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
use Filament\Tables\Filters\SelectFilter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Produk (Barang)';
    protected static ?string $modelLabel = 'Produk ';
    protected static ?string $pluralModelLabel = 'Produk ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sku')
                    ->label('Kode Barang')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(8) 
                    ->default(function () {
                        $lastProduct = Product::where('sku', 'like', 'S%')->latest('id')->first();

                        if (! $lastProduct) {
                            return 'S0000001';
                        }

                        $lastSku = $lastProduct->sku;
                        $number = (int) substr($lastSku, 1);
                        $newNumber = $number + 1;
                        return 'S' . str_pad($newNumber, 7, '0', STR_PAD_LEFT);
                    })
                    ->dehydrateStateUsing(fn (string $state): string => strtoupper($state)),
                Forms\Components\Radio::make('is_stock')
                    ->label('Kategori Penyimpanan')
                    ->boolean()
                    ->options([
                        1 => 'Barang Stok (Inventory)',
                        0 => 'Non-Stok (Langsung/Jasa)',
                    ])
                    ->default(true)
                    ->inline()
                    ->required()
                    ->reactive() 
                    ->columnSpanFull(),
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
                Forms\Components\Toggle::make('is_consumable')
                    ->label('Barang Habis Pakai (Consumable)')
                    ->default(true)
                    ->helperText('Aktifkan jika item ini habis dipakai (oli, bearing, cat). Non-aktifkan jika ini aset/alat (obeng, palu, mesin).')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('information')
                    ->label('Informasi (Mesin Pengguna)')
                    ->helperText('Contoh: MESIN STICK, MESIN JV, UHT, ROBO')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image() 
                    ->disk('public')
                    ->directory('product-images') 
                    ->maxSize(1024) 
                    ->columnSpanFull(), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->disk('public')
                    ->width(50)
                    ->height(50), 
                Tables\Columns\TextColumn::make('sku')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_stock')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Stok' : 'Non-Stok')
                    ->color(fn (bool $state) => $state ? 'info' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('information')
                    ->label('Informasi Mesin')
                    ->limit(40) 
                    ->searchable()
                    ->tooltip('Arahkan mouse untuk melihat info lengkap')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Saat Ini')
                    ->badge() 
                    ->color(fn (Product $record): string => match (true) {
                        $record->stock < $record->minimum_stock => 'danger', 
                        $record->stock == $record->minimum_stock => 'warning', 
                        default => 'success', 
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Min. Stok')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_stock')
                ->label('Kategori Penyimpanan')
                ->options([
                    1 => 'Barang Stok',
                    0 => 'Barang Non-Stok',
                ]),
                Tables\Filters\SelectFilter::make('is_consumable')
                ->label('Tipe Barang')
                ->options([
                    '1' => 'Consumable (Habis Pakai)',
                    '0' => 'Non-Consumable (Alat/Aset)',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['value'])) {
                        return $query;
                    }
            
                    return $query->where('is_consumable', (bool) $data['value']);
                }),
                SelectFilter::make('stock_status')
                    ->label('Status Stok')
                    ->options([
                        'tersedia' => 'Stok Tersedia',
                        'minimal' => 'Stok Minimal',
                        'menipis' => 'Stok Menipis',
                        'habis' => 'Stok Habis',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $status): Builder {
                                if ($status === 'tersedia') {
                                    return $query->whereRaw('stock > minimum_stock');
                                }
                                if ($status === 'minimal') {
                                    return $query->whereRaw('stock = minimum_stock');
                                }
                                if ($status === 'menipis') {
                                    return $query->whereRaw('stock < minimum_stock')->where('stock', '>', 0);
                                }
                                if ($status === 'habis') {
                                    return $query->where('stock', '<=', 0);
                                }
                                return $query;
                            }
                        );
                    })
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
