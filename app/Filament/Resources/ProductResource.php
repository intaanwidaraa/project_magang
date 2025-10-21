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
                    ->readOnly() 
                    ->placeholder('Akan dibuat otomatis setelah disimpan') 
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
                Forms\Components\TextInput::make('lifetime_penggunaan')
                    ->label('Lifetime Penggunaan')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->suffix('hari')
                    ->helperText('Sistem akan memberi notifikasi jika masa penggunaan produk telah melewati batas ini.'),
                Forms\Components\DatePicker::make('tanggal_mulai_pemakaian')
                    ->label('Tanggal Mulai Pemakaian')
                    ->disabled() 
                    ->helperText('Tanggal otomatis terisi saat barang pertama kali digunakan.'),
                Forms\Components\TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->maxLength(255)
                    ->default('pcs'), 
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
                Tables\Columns\TextColumn::make('lifetime_penggunaan')
                    ->label('Lifetime Penggunaan')
                    ->sortable()
                    ->suffix(' hari'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
