<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\SupplierItem;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Barang Masuk (PO)';
    protected static ?string $modelLabel = 'Barang Masuk (PO)';
    protected static ?string $pluralModelLabel = 'Barang Masuk (PO)';
    protected static ?string $navigationGroup = 'Manajemen Stok';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\TextInput::make('po_number')
                        ->label('Nomor PO')
                        ->default('PO-' . random_int(1000, 9999))
                        ->required(),
                    Forms\Components\Select::make('supplier_id')
                        ->label('Pemasok')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('phone_number'),
                        ])
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('products', []))
                        ->required(),
                ])->columns(2),

                Forms\Components\Section::make('Pilih Produk')
                    ->description('Pilih produk yang akan dipesan dari daftar di bawah ini.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\CheckboxList::make('products')
                            ->label('Daftar Produk dari Supplier')
                            ->options(fn (callable $get) =>
                                $get('supplier_id')
                                    ? SupplierItem::where('supplier_id', $get('supplier_id'))->pluck('nama_item', 'id')
                                    : []
                            )
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                $selectedItemIds = collect($get('items'))->pluck('supplier_item_id')->all();
                                $set('products', $selectedItemIds);
                            })
                            ->reactive()
                            ->columns(3)
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $oldItems = collect($get('items') ?? []);
                                $newItems = collect($state)->map(function ($id) use ($oldItems) {
                                    $existing = $oldItems->firstWhere('supplier_item_id', $id);
                                    $supplierItem = SupplierItem::find($id);
                                    return [
                                        'supplier_item_id' => $id,
                                        'product_id'       => $supplierItem?->product_id,
                                        'quantity'         => $existing['quantity'] ?? 1,
                                        'price'            => $supplierItem?->harga ?? 0,
                                        'total'            => ($existing['quantity'] ?? 1) * ($supplierItem?->harga ?? 0),
                                    ];
                                });
                                $set('items', $newItems->toArray());
                            }),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('addNewSupplierItem')
                                ->label('(+) Tambah Produk Baru ke Supplier Ini')
                                ->color('success')
                                ->icon('heroicon-o-plus-circle')
                                ->visible(fn (callable $get) => filled($get('supplier_id')))
                                ->modalHeading('Tambah Produk Baru')
                                ->modalButton('Simpan dan Tambahkan')
                                ->form([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Pilih Produk dari Gudang (Master)')
                                        ->options(Product::pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')->required()->label('Nama Produk Baru'),
                                            Forms\Components\TextInput::make('sku')->label('SKU (Opsional)')->readOnly()->placeholder('Akan dibuat otomatis'),
                                            Forms\Components\TextInput::make('unit')->default('pcs')->label('Satuan'),
                                        ])
                                        
                                        ->createOptionUsing(function (array $data): int {
                                            // Simpan produk baru dan kembalikan ID-nya
                                            return Product::create($data)->id;
                                        }),
                                    Forms\Components\TextInput::make('harga')
                                        ->label('Harga Beli dari Supplier Ini')
                                        ->numeric()
                                        ->required()
                                        ->prefix('Rp'),
                                ])
                                ->action(function (array $data, callable $get, callable $set) {
                                    $supplierId = $get('supplier_id');
                                    $product = Product::find($data['product_id']);
                                    $supplierItem = SupplierItem::create([
                                        'supplier_id' => $supplierId,
                                        'product_id'  => $data['product_id'],
                                        'harga'       => $data['harga'],
                                        'nama_item'   => $product->name,
                                    ]);
                                    $currentProducts = $get('products');
                                    $currentProducts[] = $supplierItem->id;
                                    $set('products', $currentProducts);
                                    Notification::make()
                                        ->title('Produk baru berhasil ditambahkan')
                                        ->success()
                                        ->send();
                                }),
                        ])->alignEnd(),
                    ]),

                Forms\Components\Repeater::make('items')
                    ->label('Detail Barang Pesanan')
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\Select::make('supplier_item_id')
                            ->label('Nama Produk')
                            ->options(fn (callable $get) => SupplierItem::where('supplier_id', $get('../../supplier_id'))->pluck('nama_item', 'id'))
                            ->disabled()->dehydrated(),
                        Forms\Components\TextInput::make('quantity')->label('Jumlah')->numeric()->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, $get) => $set('total', ($state ?? 0) * ($get('price') ?? 0))),
                        Forms\Components\TextInput::make('price')->label('Harga')->numeric()->disabled()->dehydrated(),
                        Forms\Components\TextInput::make('total')->label('Total')->numeric()->disabled()->dehydrated(),
                    ])
                    ->columns(4)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $grandTotal = collect($state)->sum(fn($item) => $item['total'] ?? 0);
                        $set('grand_total', $grandTotal);
                    }),

                Forms\Components\TextInput::make('grand_total')->label('Grand Total')->numeric()->disabled()->dehydrated(),
                Forms\Components\Textarea::make('notes')->label('Keterangan')->rows(3)->columnSpanFull(),
                Forms\Components\Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options(['po' => 'PO','cash' => 'Cash','urgent' => 'Urgent'])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')->label('Nomor PO')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Pemasok')->searchable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn($state) => strtoupper($state)),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR', true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ordered' => 'warning',
                        'completed' => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Di dalam Actions
Tables\Actions\Action::make('complete')
    ->label('Terima Barang')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->requiresConfirmation()
    ->action(function (PurchaseOrder $record) {
        DB::transaction(function () use ($record) {
            // '$record->items' sekarang akan berisi 'product_id'
            foreach ($record->items as $item) {
                $productId = $item['product_id'] ?? null;
                
                // Pengecekan ini sekarang tidak akan gagal lagi
                if (!$productId) {
                    continue;
                }

                $product = Product::find($productId);
                if ($product) {
                    $qty = $item['quantity'] ?? 0;
                    
                    // Baris ini sekarang akan berjalan dengan benar
                    $product->increment('stock', $qty);

                    StockMovement::create([
                        'product_id'     => $product->id,
                        'type'           => 'in',
                        'quantity'       => $qty,
                        'reference_type' => PurchaseOrder::class,
                        'reference_id'   => $record->id,
                    ]);
                }
            }

            $record->update(['status' => 'completed']);
        });

        Notification::make()
            ->title('Barang diterima, stok telah diupdate!')
            ->success()
            ->send();
    })
    ->visible(fn(PurchaseOrder $record): bool => $record->status === 'ordered'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
