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
use Barryvdh\DomPDF\Facade\Pdf; // [1] Import class PDF
use Symfony\Component\HttpFoundation\StreamedResponse; // [2] Import class StreamedResponse

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
                                        })->filter(); // Hapus item yang null

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
                                                    // ===== TAMBAHKAN FIELD INI =====
                                                    Forms\Components\TextInput::make('lifetime_penggunaan')
                                                        ->label('Lifetime Penggunaan')
                                                        ->numeric()
                                                        ->required()
                                                        ->default(0)
                                                        ->suffix('hari')
                                                        ->helperText('Masa ideal penggunaan produk dalam hari.'),
                                                    // ===== AKHIR PENAMBAHAN =====
                                                    Forms\Components\TextInput::make('minimum_stock')->label('Stok Minimum')->numeric()->default(0),
                                                ])
                                                // ===== TAMBAHKAN BAGIAN INI =====
                                                ->createOptionUsing(function (array $data): int {
                                                    $newProduct = Product::create($data);
                                                    // Ingat: Model Product Anda akan otomatis membuat SKU saat event 'creating'
                                                    return $newProduct->id;
                                                })
                                                // ===== AKHIR PENAMBAHAN =====
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
                                             // Ambil nilai 'unit'
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
                                
                                // ===== GRAND TOTAL DIPINDAHKAN KE SINI =====
                                Forms\Components\TextInput::make('grand_total')
                                    ->label('Total Pesanan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])->columnSpan(2),

                    // ===== SIDEBAR (KANAN) - Memakai 1 dari 3 kolom =====
                    Forms\Components\Group::make()->schema([
                        Forms\Components\Section::make('Ringkasan & Opsi')
                            ->schema([
                                Forms\Components\Placeholder::make('estimated_arrival_info')
                                    ->label('Estimasi Kedatangan')
                                    ->content(function () {
                                        $eta = Carbon::now()->addDays(14)->locale('id_ID'); // <-- TAMBAHKAN INI
                                        return $eta->translatedFormat('l, d F Y') . ' (14 hari dari sekarang)';
                                    }),
                                // ... komponen grand_total sudah dipindahkan dari sini
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
                Tables\Columns\TextColumn::make('po_number')->label('Nomor PO')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Pemasok')->searchable(),
                Tables\Columns\TextColumn::make('items')
                    ->label('Item Produk')
                    ->listWithLineBreaks() // Menampilkan setiap item di baris baru
                    ->limitList(3)         // Batasi tampilan awal 
                    ->state(function (PurchaseOrder $record): array {
                        // Cek jika tidak ada item, kembalikan array kosong
                        if (empty($record->items)) {
                            return [];
                        }

                        // Ubah array 'items' dari record menjadi collection
                        $items = collect($record->items);

                        // 1. Ambil semua ID item supplier dalam satu kali jalan
                        $itemIds = $items->pluck('supplier_item_id')->unique();

                        // 2. Ambil semua data produk dari database dalam satu query
                        //    untuk menghindari N+1 problem (query berulang-ulang)
                        $supplierItems = \App\Models\SupplierItem::whereIn('id', $itemIds)
                            ->get()
                            ->keyBy('id'); // Jadikan ID sebagai key untuk pencarian mudah

                        // 3. Buat string format "Nama (Jumlah pcs)" untuk setiap item
                        return $items->map(function ($item) use ($supplierItems) {
                            $id = $item['supplier_item_id'];
                            $quantity = $item['quantity'] ?? 0;
                            
                            // [1] Ambil data 'unit' dari array item, dengan 'pcs' sebagai fallback
                            $unit = $item['unit'] ?? 'pcs'; 

                            $name = $supplierItems->get($id)?->nama_item ?? 'Produk tidak ditemukan';
                            
                            // [2] Gunakan variabel $unit di sini, bukan 'pcs' yang di-hardcode
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
                        
                        // Ambil data harga dan format satu per satu
                        return collect($record->items)->map(function ($item) {
                            $price = $item['price'] ?? 0;
                            // Format angka menjadi string dengan format Rupiah
                            return 'Rp ' . number_format($price, 2, ',', '.');
                        })->all();
                    }),
                
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Pesanan')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->prefix('Rp ') // Menambahkan 'Rp ' di depan angka
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ordered' => 'warning',
                        'completed' => 'success',
                        default => 'gray'
                    }),
                
                // ===== AWAL PERUBAHAN =====
                Tables\Columns\TextColumn::make('duration') // [1] Ganti nama teknis kolom
                    ->label('Durasi') // [2] Ganti label header tabel
                    ->badge()
                    ->state(function (PurchaseOrder $record): int {
                        // [3] Logika perhitungan durasi
                        if ($record->status === 'completed') {
                            // Jika SUDAH diterima, hitung selisih dari tanggal dibuat sampai tanggal diupdate (diterima)
                            return $record->created_at->startOfDay()->diffInDays($record->updated_at->startOfDay()) + 1;
                        }
                        // Jika MASIH dipesan, hitung selisih dari tanggal dibuat sampai hari ini
                        return $record->created_at->startOfDay()->diffInDays(now()->startOfDay()) + 1;
                    })
                    ->formatStateUsing(function (int $state, PurchaseOrder $record): string {
                        // [4] Logika format teks yang ditampilkan
                        if ($record->status === 'completed') {
                            return "Tiba dalam {$state} hari";
                        }

                        if ($state > 14) { // Estimasi kedatangan 14 hari
                            return 'Terlambat (' . ($state - 14) . ' hari)';
                        }
                        
                        return "Hari ke-{$state}";
                    })
                    ->color(function (int $state, PurchaseOrder $record): string {
                        // [5] Logika pewarnaan badge
                        if ($record->status === 'completed') {
                            return 'success'; // Warna hijau jika sudah tiba
                        }
                        
                        if ($state > 14) {
                            return 'danger'; // Warna merah jika terlambat
                        }

                        return 'info'; // Warna biru jika masih dalam proses
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal PO')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                    //di delete after this
                    //->label('Tanggal PO')
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

    // ===== [3] AWAL PENAMBAHAN AKSI CETAK PDF =====
                Tables\Actions\Action::make('printInvoice')
                    ->label('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (PurchaseOrder $record): StreamedResponse {
                        // Memuat view blade untuk invoice dan mengirim data record
                        $pdf = PDF::loadView('invoices.purchase_order', compact('record'));
                        
                        // Mengirimkan file PDF sebagai download langsung ke browser
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'invoice-' . $record->po_number . '.pdf');
                    })
                    // Tombol ini hanya akan terlihat jika status PO sudah 'completed'
                    ->visible(fn (PurchaseOrder $record): bool => $record->status === 'completed'),
                // ===== AKHIR PENAMBAHAN AKSI CETAK PDF =====

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
