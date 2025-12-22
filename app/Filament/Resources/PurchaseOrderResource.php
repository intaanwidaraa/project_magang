<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Supplier; 
use App\Models\SupplierItem;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Tables\Columns\Summarizers\Sum; 
use Filament\Tables\Filters\SelectFilter;

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
                Forms\Components\Section::make('Informasi Pembelian')
                    ->schema([
                        Forms\Components\TextInput::make('po_number')
                            ->label('Nomor FPPB')
                            ->placeholder('Masukkan nomor secara manual')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => (PurchaseOrder::latest('id')->first()->po_number ?? 0) + 1),
                        
                        Forms\Components\DatePicker::make('created_at')
                            ->label('Tanggal Pembelian')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('budget_start_date')
                            ->label('Periode Budget Mulai')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('budget_end_date')
                            ->label('Periode Budget Selesai')
                            ->required()
                            ->default(now()->addDays(5)) 
                            ->minDate(fn (callable $get) => $get('budget_start_date')),
                        
                        Forms\Components\TextInput::make('requester_info')
                            ->label('Dept / Cost Centre')
                            ->placeholder('Contoh: ENGINEERING / 450')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('supplier_id')
                            ->default(fn() => Supplier::first()?->id), 

                    ])->columns(2),

                Forms\Components\Section::make('Pilih Produk (Mode Cepat)')
                    ->description('Pilih supplier, lalu centang produk. Jika produk belum ada, klik tombol tambah di bawah list.')
                    ->schema([
                        
                        Forms\Components\Select::make('filter_supplier_id')
                            ->label('Pilih Supplier') 
                            ->options(Supplier::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->dehydrated(false) 
                            
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label('Nama Supplier')->required(),
                                Forms\Components\TextInput::make('phone_number')->label('Nomor Telepon')->required(),
                                Forms\Components\TextInput::make('address')->label('Alamat')->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Supplier::create($data)->id;
                            })

                            ->afterStateUpdated(function (callable $set) {
                                $set('temp_selected_products', []); 
                            })
                            ->columnSpanFull(),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\CheckboxList::make('temp_selected_products')
                                    ->hiddenLabel()
                                    ->options(function (callable $get) {
                                        $supplierId = $get('filter_supplier_id');
                                        if (!$supplierId) return [];
                                        
                                        return SupplierItem::where('supplier_id', $supplierId)
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [$item->id => "{$item->nama_item} - Rp " . number_format($item->harga, 0, ',', '.')];
                                            });
                                    })
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns(3)
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $currentSupplierId = $get('filter_supplier_id');
                                        if (!$currentSupplierId) return;

                                        $currentRepeaterItems = collect($get('items') ?? []);
                                        $otherItems = $currentRepeaterItems->filter(fn($item) => ($item['supplier_id'] ?? null) != $currentSupplierId);
                                        $existingMyItems = $currentRepeaterItems
                                            ->filter(fn($item) => ($item['supplier_id'] ?? null) == $currentSupplierId)
                                            ->keyBy('supplier_item_id');

                                        $newItemsFromCheckbox = collect($state)->map(function ($supplierItemId) use ($currentSupplierId, $existingMyItems) {
                                            if ($existingMyItems->has($supplierItemId)) return $existingMyItems->get($supplierItemId);
                                            $dbItem = SupplierItem::with('product')->find($supplierItemId);
                                            if (!$dbItem) return null;

                                            return [
                                                'supplier_id'      => $currentSupplierId,
                                                'supplier_item_id' => $dbItem->id,
                                                'product_id'       => $dbItem->product_id,
                                                'coa_name'         => null,
                                                'quantity'         => 1,
                                                'unit'             => $dbItem->product?->unit ?? 'PCS',
                                                'price'            => $dbItem->harga,
                                                'total'            => $dbItem->harga,
                                            ];
                                        })->filter();

                                        $finalItems = $newItemsFromCheckbox->merge($otherItems)->values()->toArray();
                                        $set('items', $finalItems);
                                        $set('grand_total', collect($finalItems)->sum('total'));
                                        
                                        if (count($finalItems) > 0) $set('supplier_id', $finalItems[0]['supplier_id']);
                                    }),
                            ])
                            ->visible(fn (callable $get) => filled($get('filter_supplier_id')))
                            ->extraAttributes([
                                'style' => 'max-height: 300px; overflow-y: auto; border: 1px solid #e5e7eb; padding: 1rem; border-radius: 0.5rem; background-color: #fff;', 
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('addNewSupplierItem')
                                ->label('(+) Tambah Produk Baru ke Supplier Ini')
                                ->color('success')
                                ->icon('heroicon-o-plus-circle')
                                ->visible(fn (callable $get) => filled($get('filter_supplier_id')))
                                ->form([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Pilih dari Master Produk')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')->label('Nama Produk')->required(),
                                            Forms\Components\TextInput::make('sku')->label('SKU')->required(),
                                            Forms\Components\Radio::make('is_stock')
                                                ->label('Tipe Barang')
                                                ->boolean()
                                                ->options([
                                                    1 => 'Stok (Inventory)',
                                                    0 => 'Non-Stok (Jasa/Langsung)',
                                                ])
                                                ->default(true)
                                                ->inline()
                                                ->reactive()
                                                ->required(),
                                            Forms\Components\TextInput::make('unit')->label('Satuan')->default('PCS')->required(),
                                            Forms\Components\TextInput::make('minimum_stock')->label('Stok Minimum')->numeric()->default(1),
                                        ])
                                        ->createOptionUsing(fn (array $data) => Product::create($data)->id)
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('nama_item', Product::find($state)?->name)),
                                    
                                    Forms\Components\TextInput::make('nama_item')->label('Nama Item (Supplier)')->required(),
                                    Forms\Components\TextInput::make('harga')->label('Harga Supplier')->numeric()->prefix('Rp')->required(),
                                ])
                                ->action(function (array $data, callable $get, callable $set) {
                                    $currentSupplierId = $get('filter_supplier_id');
                                    
                                    $newItem = SupplierItem::create([
                                        'supplier_id' => $currentSupplierId,
                                        'product_id' => $data['product_id'],
                                        'nama_item' => $data['nama_item'],
                                        'harga' => $data['harga'],
                                    ]);

                                    $currentRepeaterItems = collect($get('items') ?? []);
                                    $dbProduct = Product::find($data['product_id']);

                                    $newItemRow = [
                                        'supplier_id'      => $currentSupplierId,
                                        'supplier_item_id' => $newItem->id,
                                        'product_id'       => $newItem->product_id,
                                        'coa_name'         => null,
                                        'quantity'         => 1,
                                        'unit'             => $dbProduct->unit ?? 'PCS',
                                        'price'            => $newItem->harga,
                                        'total'            => $newItem->harga,
                                    ];

                                    $finalItems = collect([$newItemRow])->merge($currentRepeaterItems)->values()->toArray();

                                    $set('items', $finalItems);
                                    $set('grand_total', collect($finalItems)->sum('total'));
                                    
                                    if (count($finalItems) > 0) $set('supplier_id', $finalItems[0]['supplier_id']);

                                    $currentSelection = $get('temp_selected_products') ?? [];
                                    $currentSelection[] = $newItem->id;
                                    $set('temp_selected_products', $currentSelection);

                                    Notification::make()->title('Produk ditambahkan!')->success()->send();
                                }),
                        ])->alignEnd(),
                    ]),

                Forms\Components\Section::make('Rincian Pembelian Final')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Daftar Barang')
                            ->schema([
                                Forms\Components\Hidden::make('product_id'),
                                
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->options(Supplier::all()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')->required(),
                                        Forms\Components\TextInput::make('phone_number')->required(),
                                        Forms\Components\TextInput::make('address')->required(),
                                    ])
                                    ->createOptionUsing(fn (array $data) => Supplier::create($data)->id)
                                    ->afterStateUpdated(fn (callable $set) => $set('supplier_item_id', null))
                                    ->columnSpan(3),

                                Forms\Components\Select::make('supplier_item_id')
                                    ->label('Barang')
                                    ->options(fn (callable $get) => 
                                        $get('supplier_id') 
                                        ? SupplierItem::where('supplier_id', $get('supplier_id'))->pluck('nama_item', 'id') 
                                        : []
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $item = SupplierItem::with('product')->find($state);
                                        if ($item) {
                                            $set('product_id', $item->product_id);
                                            $set('price', $item->harga);
                                            $set('unit', $item->product?->unit ?? 'PCS');
                                            $set('total', $item->harga * ($get('quantity') ?? 1));
                                        }
                                    })
                                    ->columnSpan(4), 

                                Forms\Components\Select::make('coa_name')
                                    ->label('COA')
                                    ->options([
                                        'PEMAKAIAN PRODUKSI (100.05.110)' => 'PEMAKAIAN PRODUKSI (100.05.110)',
                                        'SISA PRODUKSI (100.05.110)' => 'SISA PRODUKSI (100.05.110)',
                                        'ASET DALAM PENYELESAIAN (110.01.100)' => 'ASET DALAM PENYELESAIAN (110.01.100)',
                                        'PENJUALAN TUNAI (420.01.101)' => 'PENJUALAN TUNAI (420.01.101)',
                                        'RETUR PEMBELIAN BAHAN BAKU (430.02.107)' => 'RETUR PEMBELIAN BAHAN BAKU (430.02.107)',
                                        'KEPERLUAN PABRIKASI (430.02.109)' => 'KEPERLUAN PABRIKASI (430.02.109)',
                                        'RUSAK, SUSUT DAN HEMAT (430.02.109)' => 'RUSAK, SUSUT DAN HEMAT (430.02.109)',
                                        'SAMPLE (430.02.109)' => 'SAMPLE (430.02.109)',
                                        'REPACKING DI DISTRIBUTOR (430.02.109)' => 'REPACKING DI DISTRIBUTOR (430.02.109)',
                                        'GANTI KARDUS (430.02.109)' => 'GANTI KARDUS (430.02.109)',
                                        'PENJUALAN BAHAN BAKU (430.02.110)' => 'PENJUALAN BAHAN BAKU (430.02.110)',
                                        'ADJUSTMENT STOCK (430.02.111)' => 'ADJUSTMENT STOCK (430.02.111)',
                                        'PEMAKAIAN BATU BARA (450.02.106)' => 'PEMAKAIAN BATU BARA (450.02.106)',
                                        'BIAYA KEPERLUAN PRODUKSI (450.02.119)' => 'BIAYA KEPERLUAN PRODUKSI (450.02.119)',
                                        'PERBAIKAN BANGUNAN PABRIK (450.02.111)' => 'PERBAIKAN BANGUNAN PABRIK (450.02.111)',
                                        'PERBAIKAN MESIN PRODUKSI (450.02.112)' => 'PERBAIKAN MESIN PRODUKSI (450.02.112)',
                                        'PERBAIKAN PERALATAN PABRIK (450.02.113)' => 'PERBAIKAN PERALATAN PABRIK (450.02.113)',
                                        'PERBAIKAN KEPERLUAN PRODUKSI (450.02.121)' => 'PERBAIKAN KEPERLUAN PRODUKSI (450.02.121)',
                                        'PERBAIKAN MESIN PACKING (450.02.126)' => 'PERBAIKAN MESIN PACKING (450.02.126)',
                                        'PERBAIKAN UTILITY (450.02.127)' => 'PERBAIKAN UTILITY (450.02.127)',
                                        'ATK (500.05.100)' => 'ATK (500.05.100)',
                                        'P3K (500.06.101)' => 'P3K (500.06.101)',
                                        'PERBAIKAN BANGUNAN (510.05.100)' => 'PERBAIKAN BANGUNAN (510.05.100)',
                                        'PERBAIKAN INVENTARIS KANTOR (510.05.101)' => 'PERBAIKAN INVENTARIS KANTOR (510.05.101)',
                                        'BIAYA PEMELIHARAAN INVENTARIS KANTOR (510.05.101)' => 'BIAYA PEMELIHARAAN INVENTARIS KANTOR (510.05.101)',
                                        'PERBAIKAN MOBIL BAGIAN KANTOR (510.06.101)' => 'PERBAIKAN MOBIL BAGIAN KANTOR (510.06.101)',
                                        'PERBAIKAN MOTOR BAGIAN KANTOR (510.06.102)' => 'PERBAIKAN MOTOR BAGIAN KANTOR (510.06.102)',
                                        'BIAYA RUMAH HARJA MULIA (600.02.100)' => 'BIAYA RUMAH HARJA MULIA (600.02.100)',
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jml')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('total', ($state ?? 0) * ($get('price') ?? 0));
                                        $set('../../grand_total', collect($get('../../items'))->sum('total'));
                                    })
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('unit')->label('Sat')->disabled()->dehydrated()->columnSpan(1),

                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->dehydrated()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('total', ($state ?? 0) * ($get('quantity') ?? 0));
                                        $set('../../grand_total', collect($get('../../items'))->sum('total'));
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('total')->label('Total')->numeric()->prefix('Rp')->disabled()->dehydrated()->columnSpan(2), 
                            ])
                            ->columns(16) 
                            ->reactive()
                            ->reorderable(false)
                            ->deleteAction(fn ($action) => $action->iconButton()),

                        Forms\Components\TextInput::make('grand_total')
                            ->label('Total Pesanan')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: bold; text-align: right;']),
                    ]),

                    Forms\Components\Section::make('Opsi & Keterangan')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Placeholder::make('estimated_arrival_info')
                                ->label('Estimasi Kedatangan')
                                ->content(fn () => Carbon::now()->addDays(14)->locale('id_ID')->translatedFormat('l, d F Y') . ' (14 hari)'),
                            Forms\Components\Select::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->options(['po' => 'PO', 'cash' => 'Cash', 'urgent' => 'Urgent'])
                                ->required(),
                        ]),
                        Forms\Components\Textarea::make('notes')->label('Catatan')->rows(3)->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('po_number')
                    ->label('Nomor FPPB')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_supplier')
                    ->label('Pemasok')
                    ->state(function (PurchaseOrder $record) {
                        if (empty($record->items)) return '-';
                        $items = is_string($record->items) ? json_decode($record->items, true) : $record->items;
                        $ids = collect($items)->pluck('supplier_id')->filter()->unique();
                        return Supplier::whereIn('id', $ids)->pluck('name')->join(', ');
                    })
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('items')
                    ->label('Nama Barang')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) return [];
                        $items = is_string($record->items) ? json_decode($record->items, true) : $record->items;
                        $itemIds = collect($items)->pluck('supplier_item_id')->unique();
                        $supplierItems = SupplierItem::whereIn('id', $itemIds)->get()->keyBy('id');

                        return collect($items)->map(function ($item) use ($supplierItems) {
                            $id = $item['supplier_item_id'];
                            $name = $supplierItems->get($id)?->nama_item ?? 'Produk tidak ditemukan';
                            $qty = $item['quantity'] ?? 0;
                            $unit = $item['unit'] ?? 'pcs';
                            return "{$name} ({$qty} {$unit})";
                        })->all();
                    }),

                Tables\Columns\TextColumn::make('prices')
                    ->label('Harga Satuan')
                    ->listWithLineBreaks()
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) return [];
                        $items = is_string($record->items) ? json_decode($record->items, true) : $record->items;
                        return collect($items)->map(fn ($item) => 'Rp ' . number_format($item['price'] ?? 0, 2, ',', '.'))->all();
                    }),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Pesanan')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('Rp ')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 2)->money('IDR')),

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
                SelectFilter::make('status')->options(['ordered' => 'Ordered', 'completed' => 'Completed']),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari tanggal'),
                        DatePicker::make('created_until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['created_from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['created_until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
                    )
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('whatsapp')
                    ->label('Hubungi')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->form(function (PurchaseOrder $record) {
                        $items = is_string($record->items) ? json_decode($record->items, true) : $record->items;
                        $ids = collect($items)->pluck('supplier_id')->unique();
                        return [
                            Forms\Components\Select::make('target_supplier_id')
                                ->label('Pilih Supplier')
                                ->options(Supplier::whereIn('id', $ids)->pluck('name', 'id'))
                                ->required()
                        ];
                    })
                    ->action(function (array $data, PurchaseOrder $record) {
                        $supplier = Supplier::find($data['target_supplier_id']);
                        $phone = $supplier->phone_number;
                        if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
                        return redirect()->to("https://wa.me/{$phone}?text=Halo, update PO {$record->po_number}?");
                    }),
                
                Tables\Actions\Action::make('complete')
                    ->label('Terima')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        DB::transaction(function () use ($record) {
                            $items = is_string($record->items) ? json_decode($record->items, true) : $record->items;
                            foreach ($items as $item) {
                                $product = Product::find($item['product_id'] ?? null);
                                if ($product) {
                                    $product->increment('stock', $item['quantity']);
                                    StockMovement::create(['product_id' => $product->id, 'type' => 'in', 'quantity' => $item['quantity'], 'reference_type' => PurchaseOrder::class, 'reference_id' => $record->id]);
                                }
                            }
                            $record->update(['status' => 'completed']);
                        });
                        Notification::make()->title('Stok Updated')->success()->send();
                    })
                    ->visible(fn($record) => $record->status === 'ordered'),

                Tables\Actions\Action::make('printFPPB')
                    ->label('FPPB')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->action(function (PurchaseOrder $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo Pdf::loadView('invoices.fppb', compact('record'))->output();
                        }, "FPPB-{$record->po_number}.pdf");
                    }),

                Tables\Actions\Action::make('printInvoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (PurchaseOrder $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo Pdf::loadView('invoices.purchase_order', compact('record'))->output();
                        }, "invoice-{$record->po_number}.pdf");
                    })
                    ->visible(fn ($record) => $record->status === 'completed'),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}