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
use Illuminate\Support\Number;
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Tables\Columns\Summarizers\Sum; 

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $modelLabel = 'Barang Masuk';
    protected static ?string $pluralModelLabel = 'Barang Masuk';
    protected static ?string $navigationGroup = 'Manajemen Stok';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Membuat layout grid utama dengan 3 kolom
                Forms\Components\Grid::make()->columns(3)->schema([
                    Forms\Components\Group::make()->schema([
                        Forms\Components\Section::make('Informasi Pesanan')
                            ->schema([
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
                                        Forms\Components\TextInput::make('phone_number')->required(),
                                        Forms\Components\TextInput::make('address')->required(),
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('products', []);
                                        $set('items', []);
                                    })
                                    ->required(),
                            ])->columns(2),

                        Forms\Components\Section::make('Detail Barang Pesanan')
                            ->description('Pilih produk dari daftar, lalu atur jumlahnya di bawah.')
                            ->schema([
                                Forms\Components\CheckboxList::make('products')
                                    ->label('Pilih Produk dari Supplier')
                                    ->searchable()
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

                                        // Ambil semua data SupplierItem beserta relasi product-nya dalam satu query
                                        $selectedSupplierItems = SupplierItem::with('product')->whereIn('id', $state)->get()->keyBy('id');

                                        $newItems = collect($state)->map(function ($id) use ($oldItems, $selectedSupplierItems) { // <-- Variabel ditambahkan di sini
                                            $existing = $oldItems->firstWhere('supplier_item_id', $id);
                                            $supplierItem = $selectedSupplierItems->get($id);

                                            if (!$supplierItem) {
                                                return null; // Lewati jika item tidak ditemukan
                                            }

                                            return [
                                                'supplier_item_id' => $id,
                                                'product_id'       => $supplierItem->product_id,
                                                'quantity'         => $existing['quantity'] ?? 1,
                                                'price'            => $supplierItem->harga ?? 0,
                                                // Mengambil 'unit' dari relasi product yang sudah di-load
                                                'unit'             => $supplierItem->product?->unit ?? 'pcs',
                                                'total'            => ($existing['quantity'] ?? 1) * ($supplierItem->harga ?? 0),
                                            ];
                                        })->filter();

                                        $set('items', $newItems->values()->toArray());
                                        $grandTotal = $newItems->sum('total');
                                        $set('grand_total', $grandTotal);
                                    }),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('addNewSupplierItem')
                                        ->label('(+) Tambah Produk Baru ke Supplier Ini')
                                        ->color('success')
                                        ->icon('heroicon-o-plus-circle')
                                        ->visible(fn (callable $get) => filled($get('supplier_id')))
                                        ->form([
                                            Forms\Components\Select::make('product_id')
                                                ->label('Pilih dari Master Produk')
                                                ->options(Product::query()->pluck('name', 'id'))
                                                ->searchable()
                                                ->required()
                                                ->required()
                                                ->reactive()
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('name')
                                                        ->label('Nama Produk')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('sku')
                                                        ->label('SKU (Kode Unik)')
                                                        ->readOnly()
                                                        ->placeholder('Akan dibuat otomatis setelah disimpan'),
                                                    Forms\Components\TextInput::make('unit')->label('Satuan (pcs, box, dll)')->default('pcs')->required(),
                                                    Forms\Components\TextInput::make('lifetime_penggunaan')
                                                        ->label('Lifetime Penggunaan')
                                                        ->numeric()
                                                        ->required()
                                                        ->default(0)
                                                        ->suffix('hari')
                                                        ->helperText('Masa ideal penggunaan produk dalam hari.'),
                                                    Forms\Components\TextInput::make('minimum_stock')->label('Stok Minimum')->numeric()->default(0),
                                                ])
                                                ->createOptionUsing(function (array $data): int {
                                                    $newProduct = Product::create($data);
                                                    return $newProduct->id;
                                                })
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $product = Product::find($state);
                                                    if($product) {
                                                        $set('nama_item', $product->name);
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('nama_item')
                                                ->label('Nama Item (versi supplier)')
                                                ->helperText('Nama produk ini yang akan muncul di daftar pilihan.')
                                                ->required(),
                                            Forms\Components\TextInput::make('harga')
                                                ->label('Harga dari Supplier')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->required(),
                                        ])
                                        ->action(function (array $data, callable $get, callable $set) {
                                            $supplierId = $get('supplier_id');

                                            $newSupplierItem = SupplierItem::create([
                                                'supplier_id' => $supplierId,
                                                'product_id' => $data['product_id'],
                                                'nama_item' => $data['nama_item'],
                                                'harga' => $data['harga'],
                                            ]);

                                            $currentSelectedProducts = $get('products');
                                            $currentSelectedProducts[] = $newSupplierItem->id;
                                            $set('products', $currentSelectedProducts);

                                            Notification::make()
                                                ->title('Produk baru berhasil ditambahkan ke supplier')
                                                ->success()
                                                ->send();
                                        })
                                ])->alignEnd(),


                                Forms\Components\Repeater::make('items')
                                    ->label('Rincian Pesanan')
                                    ->schema([
                                        Forms\Components\Hidden::make('product_id'),
                                        Forms\Components\Select::make('supplier_item_id')
                                            ->label('Nama Produk')
                                            ->options(fn (callable $get) => SupplierItem::where('supplier_id', $get('../../supplier_id'))->pluck('nama_item', 'id'))
                                            ->disabled()->dehydrated(),

                                        Forms\Components\TextInput::make('quantity')->label('Jumlah')->numeric()->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $set('total', ($state ?? 0) * ($get('price') ?? 0));
                                                $allItems = $get('../../items');
                                                $grandTotal = collect($allItems)->sum(fn($item) => $item['total'] ?? 0);
                                                $set('../../grand_total', $grandTotal);
                                            }),
                                        Forms\Components\TextInput::make('unit')
                                            ->label('Satuan')
                                            ->disabled()
                                            ->dehydrated(),
                                        Forms\Components\TextInput::make('price')->label('Harga per Item')->numeric()->prefix('Rp')->disabled()->dehydrated(),
                                        Forms\Components\TextInput::make('total')->label('Total')->numeric()->prefix('Rp')->disabled()->dehydrated(),
                                    ])
                                    ->columns(5)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $grandTotal = collect($state)->sum(fn($item) => $item['total'] ?? 0);
                                        $set('grand_total', $grandTotal);
                                    })
                                    ->reorderable(false)
                                    ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->iconButton()),

                                Forms\Components\TextInput::make('grand_total')
                                    ->label('Total Pesanan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])->columnSpan(2),

                    Forms\Components\Group::make()->schema([
                        Forms\Components\Section::make('Ringkasan & Opsi')
                            ->schema([
                                Forms\Components\Placeholder::make('estimated_arrival_info')
                                    ->label('Estimasi Kedatangan')
                                    ->content(function () {
                                        $eta = Carbon::now()->addDays(14)->locale('id_ID');
                                        return $eta->translatedFormat('l, d F Y') . ' (14 hari dari sekarang)';
                                    }),
                                Forms\Components\Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->options(['po' => 'PO', 'cash' => 'Cash', 'urgent' => 'Urgent'])
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Keterangan')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan Tambahan (Opsional)')
                                    ->rows(4),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal PO')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('po_number')->label('Nomor PO')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Pemasok')->searchable(),
                Tables\Columns\TextColumn::make('items')
                    ->label('Item Produk')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->state(function (PurchaseOrder $record): array {

                        if (empty($record->items)) {
                            return [];
                        }

                        $items = collect($record->items);

                        $itemIds = $items->pluck('supplier_item_id')->unique();

                        $supplierItems = \App\Models\SupplierItem::whereIn('id', $itemIds)
                            ->get()
                            ->keyBy('id');

                        return $items->map(function ($item) use ($supplierItems) {
                            $id = $item['supplier_item_id'];
                            $quantity = $item['quantity'] ?? 0;
                            $unit = $item['unit'] ?? 'pcs';
                            $name = $supplierItems->get($id)?->nama_item ?? 'Produk tidak ditemukan';
                            return "{$name} ({$quantity} {$unit})";
                        })->all();
                    }),

                Tables\Columns\TextColumn::make('prices')
                    ->label('Harga per Item')
                    ->listWithLineBreaks()
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) {
                            return [];
                        }
                        return collect($record->items)->map(function ($item) {
                            $price = $item['price'] ?? 0;
                            return 'Rp ' . number_format($price, 2, ',', '.');
                        })->all();
                    }),

                // [UBAH BAGIAN INI] Tambahkan ->summarize() di bawah
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Pesanan')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->prefix('Rp ')
                    ->sortable()
                    ->summarize(Sum::make()
                        ->label('Total Pengeluaran')
                        ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                        ->money('IDR')),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ordered' => 'warning',
                        'completed' => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Durasi')
                    ->badge()
                    ->state(function (PurchaseOrder $record): int {
                        if ($record->status === 'completed') {
                            return $record->created_at->startOfDay()->diffInDays($record->updated_at->startOfDay()) + 1;
                        }
                        return $record->created_at->startOfDay()->diffInDays(now()->startOfDay()) + 1;
                    })
                    ->formatStateUsing(function (int $state, PurchaseOrder $record): string {
                        if ($record->status === 'completed') {
                            return "Tiba dalam {$state} hari";
                        }

                        if ($state > 14) {
                            return 'Terlambat (' . ($state - 14) . ' hari)';
                        }

                        return "Hari ke-{$state}";
                    })
                    ->color(function (int $state, PurchaseOrder $record): string {
                        if ($record->status === 'completed') {
                            return 'success';
                        }

                        if ($state > 14) {
                            return 'danger';
                        }

                        return 'info';
                    }),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari tanggal'),
                        DatePicker::make('created_until')
                            ->label('Sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->items as $item) {
                                $productId = $item['product_id'] ?? null;

                                if (!$productId) {
                                    continue;
                                }

                                $product = Product::find($productId);
                                if ($product) {
                                    $qty = $item['quantity'] ?? 0;
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

                Tables\Actions\Action::make('printInvoice')
                    ->label('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (PurchaseOrder $record): StreamedResponse {
                        $pdf = PDF::loadView('invoices.purchase_order', compact('record'));

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'invoice-' . $record->po_number . '.pdf');
                    })
                    ->visible(fn (PurchaseOrder $record): bool => $record->status === 'completed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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