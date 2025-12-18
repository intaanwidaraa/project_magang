<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product;
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
                Forms\Components\Grid::make(2) 
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Pilih Produk dari Gudang')
                            ->relationship('product', 'name') 
                            ->searchable(['name', 'sku'])
                            ->preload()
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $product = Product::find($state); 
                                if ($product) {
                                    $set('nama_item', $product->name);
                                    
                                    $set('kode_barang', $product->sku); 
                                } else {
                                    $set('nama_item', null);
                                    $set('kode_barang', null);
                                }
                            })
                            
                            
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Produk Baru')
                                    ->required()
                                    ->maxLength(255),
                                
                                
                              Forms\Components\TextInput::make('sku')
                                    ->label('Kode Barang (SKU)')
                                    ->required() // Wajib diisi manual
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

                       
                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->disabled() 
                            ->dehydrated(false) 
                    ]),

                Forms\Components\TextInput::make('nama_item')
                    ->label('Nama Item (Versi Supplier)')
                    ->required()
                    ->maxLength(255),
                
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
                    ->searchable(), 

                 Tables\Columns\TextColumn::make('harga')
                    ->label('Harga Beli')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->prefix('Rp ') 
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