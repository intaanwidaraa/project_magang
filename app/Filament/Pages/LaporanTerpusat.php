<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockRequisition;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\PurchaseOrder;
use App\Exports\ProductsExport;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PurchaseOrdersExport;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Get;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Group;
use Livewire\Attributes\On;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Str;

class LaporanTerpusat extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static string $view = 'filament.pages.laporan-terpusat';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Terpusat';
    protected static ?string $title = 'Laporan Terpusat';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'jenisLaporan' => 'stok',
            'filterPeriode' => 'harian',
            'tanggal' => now()->format('Y-m-d'),
            'product_id' => null,
            'tanggal_mulai' => now()->startOfMonth()->format('Y-m-d'),
            'tanggal_akhir' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(12)
                    ->schema([
                        Select::make('jenisLaporan')
                            ->label('Pilih Jenis Laporan')
                            ->options([
                                'stok' => 'Laporan Stok',
                                'barang_masuk' => 'Laporan Barang Masuk',
                                'keluar' => 'Laporan Barang Keluar', 
                            ])
                            ->live()
                            ->columnSpan(3),

                        
                        Select::make('status_filter_masuk') 
                            ->label('Filter Status')
                            ->options([
                                'all' => 'Semua Status',
                                'completed' => 'Completed (Diterima)',
                                'ordered' => 'Ordered (Belum Diterima)',
                            ])
                            ->default('all')
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'barang_masuk')
                            ->columnSpan(3),
                        Select::make('filterPeriodeMasuk') 
                            ->label('Filter Berdasarkan')
                            ->options([
                                'rentang_tanggal' => 'Rentang Tanggal',
                                'harian' => 'Harian',
                                'bulanan' => 'Bulanan',
                                'tahunan' => 'Tahunan',
                            ])
                            ->default('harian')
                            ->live()
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'barang_masuk')
                            ->columnSpan(3),
                        

                        
                        Select::make('filterPeriodeStok')
                            ->label('Filter Berdasarkan')
                            ->options([
                                'harian' => 'Harian',
                                'bulanan' => 'Bulanan',
                            ])
                            ->default('harian')
                            ->live()
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'stok')
                            ->columnSpan(3),
                        Select::make('product_id')
                            ->label('Filter Produk')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Semua Produk')
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'stok')
                            ->columnSpan(3),
                         DatePicker::make('tanggalStok') 
                            ->label('Pilih Tanggal')
                            ->default(now())
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'stok')
                            ->columnSpan(3),
                        

                        
                        Select::make('status_filter_keluar') 
                            ->label('Filter Status')
                            ->options([
                                'all' => 'Semua Status',
                                'completed' => 'Completed (Dikeluarkan)',
                                'pending' => 'Pending (Belum Dikeluarkan)',
                            ])
                            ->default('all')
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'keluar') // Hanya tampil jika 'keluar'
                            ->columnSpan(3),
                        Select::make('filterPeriodeKeluar') 
                            ->label('Filter Berdasarkan')
                             ->options([
                                'rentang_tanggal' => 'Rentang Tanggal',
                                'harian' => 'Harian',
                                'bulanan' => 'Bulanan',
                                'tahunan' => 'Tahunan',
                            ])
                            ->default('harian')
                            ->live()
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'keluar') // Hanya tampil jika 'keluar'
                            ->columnSpan(3),
                        

                        
                        DatePicker::make('tanggal')
                            ->label('Pilih Tanggal')
                            ->default(now())
                            ->visible(function (Get $get) {
                                $jenis = $get('jenisLaporan');
                                if ($jenis === 'stok') return false; 

                                $periodeMasuk = $get('filterPeriodeMasuk'); 
                                $periodeKeluar = $get('filterPeriodeKeluar'); 

                                
                                return ($jenis === 'barang_masuk' && $periodeMasuk !== 'rentang_tanggal') ||
                                       ($jenis === 'keluar' && $periodeKeluar !== 'rentang_tanggal');
                            })
                            ->columnSpan(3),
                        
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('tanggal_mulai')
                                    ->label('Dari Tanggal'),
                                DatePicker::make('tanggal_akhir')
                                    ->label('Sampai Tanggal'),
                            ])
                            ->visible(function (Get $get) {
                                $jenis = $get('jenisLaporan');
                                $periodeMasuk = $get('filterPeriodeMasuk'); 
                                $periodeKeluar = $get('filterPeriodeKeluar'); 
                                
                                return ($jenis === 'barang_masuk' && $periodeMasuk === 'rentang_tanggal') ||
                                       ($jenis === 'keluar' && $periodeKeluar === 'rentang_tanggal');
                            })
                            ->columnSpan(6),
                        
                        Group::make()->schema([
                            FormActions::make([
                                FormAction::make('tampilkan') 
                                    ->label('Tampilkan')
                                    ->button()
                                    ->action('tampilkan'), 
                                FormAction::make('cetakPdf')
                                    ->label('Cetak PDF')
                                    ->button()
                                    ->color('danger')
                                    ->action('cetakPdfAction'), 
                                FormAction::make('export')
                                    ->label('Ekspor ke Excel')
                                    ->button()
                                    ->color('success')
                                    ->action('exportAction'), 
                            ])->alignEnd()
                        ])->columnSpan(12),
                         
                    ])
                    ->extraAttributes(['class' => 'items-end gap-x-4']),
            ])
            ->statePath('data');
    }
    
    public function tampilkan(): void
    {
        $this->dispatch('applyFilters', filters: $this->form->getState());
    }

    #[On('applyFilters')]
    public function applyFilters(array $filters): void
    {
        $this->data = $filters;
        $this->resetPage();
    }

    protected function getTableQuery(): Builder
    {
        
        $jenisLaporan = $this->data['jenisLaporan'] ?? 'stok';

        $query = match ($jenisLaporan) {
            'stok' => Product::query(),
            'barang_masuk' => PurchaseOrder::query()->with('supplier'),
            'keluar' => StockRequisition::query(), 
            default => Product::query(), 
        };

        
        match ($jenisLaporan) {
            'stok' => $this->applyStokFilters($query),
            'barang_masuk' => $this->applyBarangMasukFilters($query),
            'keluar' => $this->applyBarangKeluarFilters($query), 
        };

        
        return $query;
    }

    
    protected function applyStokFilters(Builder $query): void
    {
        $filterPeriodeStok = $this->data['filterPeriodeStok'] ?? 'harian';
        $tanggal = $this->data['tanggalStok'] ?? now()->format('Y-m-d'); 
        $tanggalCarbon = Carbon::parse($tanggal);
        $productId = $this->data['product_id'] ?? null;

        $query->when($productId, fn($q) => $q->where('id', $productId));

        $query->with(['stockMovements' => function ($q) use ($tanggalCarbon, $filterPeriodeStok) {
            match ($filterPeriodeStok) {
                'bulanan' => $q->whereMonth('created_at', $tanggalCarbon->month)->whereYear('created_at', $tanggalCarbon->year),
                default => $q->whereDate('created_at', $tanggalCarbon),
            };
        }]);
    }

    
    protected function applyBarangMasukFilters(Builder $query): void
    {
        $statusFilter = $this->data['status_filter_masuk'] ?? 'all'; 
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        $filterPeriode = $this->data['filterPeriodeMasuk'] ?? 'harian'; 
        $kolomTanggal = 'created_at';
        $this->applyDateFilters($query, $filterPeriode, $kolomTanggal);
    }

   
    protected function applyBarangKeluarFilters(Builder $query): void
    {
        $statusFilter = $this->data['status_filter_keluar'] ?? 'all'; // Gunakan nama state yang benar
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        $filterPeriode = $this->data['filterPeriodeKeluar'] ?? 'harian'; // Gunakan nama state yang benar
        $kolomTanggal = ($statusFilter === 'completed') ? 'updated_at' : 'created_at';
        $this->applyDateFilters($query, $filterPeriode, $kolomTanggal);
    }

    
    protected function applyDateFilters(Builder $query, string $filterPeriode, string $kolomTanggal): void
    {
        match ($filterPeriode) {
            'rentang_tanggal' => $query
                ->when($this->data['tanggal_mulai'] ?? null, fn(Builder $q, $date) => $q->whereDate($kolomTanggal, '>=', $date))
                ->when($this->data['tanggal_akhir'] ?? null, fn(Builder $q, $date) => $q->whereDate($kolomTanggal, '<=', $date)),
            'bulanan' => $query->whereMonth($kolomTanggal, Carbon::parse($this->data['tanggal'] ?? now())->month)
                               ->whereYear($kolomTanggal, Carbon::parse($this->data['tanggal'] ?? now())->year),
            'tahunan' => $query->whereYear($kolomTanggal, Carbon::parse($this->data['tanggal'] ?? now())->year),
            default => $query->whereDate($kolomTanggal, Carbon::parse($this->data['tanggal'] ?? now())), 
        };
    }

    protected function getTableColumns(): array
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'stok' => [
                TextColumn::make('tanggal')
                    ->label('Periode')
                    ->state(function () {
                        $tanggal = Carbon::parse($this->data['tanggal'] ?? now());
                        $periode = $this->data['filterPeriodeStok'] ?? 'harian';
                        
                        return match ($periode) {
                            'bulanan' => $tanggal->translatedFormat('F Y'),
                            default => $tanggal->translatedFormat('d F Y'),
                        };
                    }),
                TextColumn::make('sku')->label('Kode')->searchable(),
                TextColumn::make('name')->label('Nama Barang')->searchable(),
                TextColumn::make('unit')->label('Satuan')->alignCenter(),
                TextColumn::make('stok_awal')
                    ->label('Stok Awal')
                    ->state(function (Product $record) {
                        $masukHariIni = $record->stockMovements->where('type', 'in')->sum('quantity');
                        $keluarHariIni = $record->stockMovements->where('type', 'out')->sum('quantity');
                        return ($record->stock - $masukHariIni + $keluarHariIni); 
                    })
                    ->alignRight(),
                    
                TextColumn::make('masuk')
                    ->label('Masuk')
                    ->state(fn (Product $record) => $record->stockMovements->where('type', 'in')->sum('quantity'))
                    ->alignRight(),
                    
                TextColumn::make('keluar')
                    ->label('Keluar')
                    ->state(fn (Product $record) => $record->stockMovements->where('type', 'out')->sum('quantity'))
                    ->alignRight(),
                    
                TextColumn::make('stock')
                    ->label('Stok Akhir')
                    ->state(fn(Product $record) => $record->stock)
                    ->sortable()
                    ->alignRight(),
            ],
            'barang_masuk' => [
                TextColumn::make('items_name')
                    ->label('Nama Barang')
                    ->listWithLineBreaks()
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) return [];
                        $itemIds = collect($record->items)->pluck('supplier_item_id')->unique();
                        $supplierItems = \App\Models\SupplierItem::whereIn('id', $itemIds)->get()->keyBy('id');
                        return collect($record->items)->map(fn($item) => $supplierItems->get($item['supplier_item_id'])?->nama_item ?? 'N/A')->all();
                    }),
                TextColumn::make('items_qty')
                    ->label('Qty')
                    ->listWithLineBreaks()
                    ->state(fn (PurchaseOrder $record): array => collect($record->items)->map(fn($item) => $item['quantity'] ?? 0)->all()),
                TextColumn::make('items_unit')
                    ->label('Satuan')
                    ->listWithLineBreaks()
                    ->state(fn (PurchaseOrder $record): array => collect($record->items)->map(fn($item) => $item['unit'] ?? 'pcs')->all()),
                TextColumn::make('items_price')
                    ->label('Harga Satuan')
                    ->listWithLineBreaks()
                    ->state(fn (PurchaseOrder $record): array => collect($record->items)->map(fn($item) => 'Rp ' . number_format($item['price'] ?? 0, 0, ',', '.'))->all()),
                TextColumn::make('grand_total')
                    ->label('Total Pesanan') 
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('Rp ')
                    ->alignEnd()
                    ->summarize(Sum::make()
                        ->label('Total Keseluruhan')
                        ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                        ->money('IDR')),
                TextColumn::make('notes')->label('Keterangan')->toggleable(),
                TextColumn::make('payment_method')->label('Metode Pembayaran')->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge() 
                    ->color(fn (string $state): string => match ($state) {
                        'ordered' => 'warning', 
                        'completed' => 'success', 
                        default => 'secondary',
                    }),
                TextColumn::make('supplier.name')->label('Supplier')->searchable(),
                TextColumn::make('created_at')->label('Tanggal Pemesanan')->date('d M Y')->sortable(),
                TextColumn::make('updated_at')->label('Tanggal Penerimaan')->date('d M Y')->sortable(),
            ],
            'keluar' => [
                 TextColumn::make('requester_name')->label('Nama Pengambil')->searchable(),
                 TextColumn::make('department')->label('Bagian')->sortable(),
                 TextColumn::make('shift')
                    ->label('Shift')
                    ->formatStateUsing(fn (?string $state): string => $state ? "Shift {$state}" : '-'),
                 TextColumn::make('notes')->label('Keterangan')->limit(30),
                 TextColumn::make('items')
                    ->label('Daftar Barang')
                    ->listWithLineBreaks()
                    ->limitList(3) 
                    ->state(function (StockRequisition $record): array {
                         
                        $items = $record->items;
                        if (is_string($items)) {
                            $items = json_decode($items, true);
                        } else {
                            $items = json_decode(json_encode($items), true);
                        }
                        if (empty($items) || !is_array($items)) {
                            return [];
                        }
                        $items = collect($items);
                        
                        $productIdsNeedingFetch = $items
                            ->whereNull('product_name')
                            ->pluck('product_id')
                            ->unique()
                            ->filter();

                        $products = collect();
                        if ($productIdsNeedingFetch->isNotEmpty()) {
                            
                            $products = \App\Models\Product::whereIn('id', $productIdsNeedingFetch)->get()->keyBy('id');
                        }

                        return $items->map(function ($item) use ($products) {
                            $quantity = $item['quantity'] ?? 0;
                            $productName = $item['product_name'] ?? 'Barang Dihapus'; 
                            $unit = $item['product_unit'] ?? 'pcs';

                           
                            if (($productName === 'Barang Dihapus' || is_null($productName)) && isset($item['product_id'])) {
                                $product = $products->get($item['product_id']);
                                if ($product) {
                                    $productName = $product->name;
                                    $unit = $product->unit ?? 'pcs';
                                }
                            }

                            $limitedName = Str::limit($productName, 40, '...');
                            return "{$limitedName} ({$quantity} {$unit})";
                        })->all();
                    }),
                 TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                    'pending' => 'warning',
                    'completed' => 'success',
                 }),
                 TextColumn::make('created_at')->label('Tanggal Dibuat')->date('d M Y')->sortable(),
                 TextColumn::make('updated_at')->label('Waktu Dikeluarkan')->dateTime('d M Y H:i')->sortable()
                    ->formatStateUsing(function ($state, StockRequisition $record) {
                        return $record->status === 'pending' ? '-' : ($state ? $state->format('d M Y H:i') : '-');
                    }),
            ],
            default => [],
        };
    }

    protected function getTableFilters(): array
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'barang_masuk' => [
                Filter::make('supplier')->form([ Select::make('supplier_id')->label('Pemasok')->options(Supplier::query()->pluck('name', 'id'))->searchable(), ])->query(function (Builder $query, array $data): Builder { return $query->when( $data['supplier_id'] ?? null, fn(Builder $query, $supplierId): Builder => $query->where('supplier_id', '=', $supplierId) ); }),
                
                Filter::make('created_at')->form([ DatePicker::make('created_from')->label('Dari Tanggal'), DatePicker::make('created_until')->label('Sampai Tanggal'), ])->query(function (Builder $query, array $data): Builder { return $query->when( $data['created_from'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date), )->when( $data['created_until'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date), ); }),
            
            ],
            default => [],
        };
    }

    protected function getTableHeaderActions(): array
    {
        return [];
    }
}