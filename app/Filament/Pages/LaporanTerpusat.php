<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Supplier;
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
                                'stok' => 'Laporan Stok Harian',
                                'barang_masuk' => 'Laporan Barang Masuk (PO)',
                            ])
                            ->live()
                            ->columnSpan(3),

                        Select::make('product_id')
                            ->label('Filter Produk')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Semua Produk')
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'stok')
                            ->columnSpan(3),

                        Select::make('filterPeriode')
                            ->label('Filter Berdasarkan')
                            ->options([
                                // [1] PENAMBAHAN OPSI BARU
                                'rentang_tanggal' => 'Rentang Tanggal',
                                'harian' => 'Harian',
                                'bulanan' => 'Bulanan',
                                'tahunan' => 'Tahunan',
                            ])
                            ->default('harian')
                            ->live() 
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'barang_masuk')
                            ->columnSpan(3),

                        // [2] UBAH LOGIKA VISIBILITY DATEPICKER TUNGGAL
                        DatePicker::make('tanggal')
                            ->label('Pilih Tanggal')
                            ->default(now())
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'stok' || ($get('jenisLaporan') === 'barang_masuk' && in_array($get('filterPeriode'), ['harian', 'bulanan', 'tahunan'])))
                            ->columnSpan(3),
                        
                        // [3] TAMBAHKAN GRID UNTUK RENTANG TANGGAL
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('tanggal_mulai')
                                    ->label('Dari Tanggal'),
                                DatePicker::make('tanggal_akhir')
                                    ->label('Sampai Tanggal'),
                            ])
                            ->visible(fn (Get $get) => $get('jenisLaporan') === 'barang_masuk' && $get('filterPeriode') === 'rentang_tanggal')
                            ->columnSpan(6),

                        Group::make()->schema([
                            FormActions::make([
                                FormAction::make('export')
                                    ->label('Ekspor ke Excel')
                                    ->button()
                                    ->color('success')
                                    ->action(function () {
                                        $jenisLaporan = $this->data['jenisLaporan'] ?? 'stok';
                                        if ($jenisLaporan === 'stok') {
                                            return Excel::download(new ProductsExport, 'laporan-stok.xlsx');
                                        } else {
                                            $query = $this->getFilteredTableQuery();
                                            $allItemIds = $query->get()->flatMap(fn($po) => collect($po->items)->pluck('supplier_item_id'))->unique();
                                            $supplierItems = \App\Models\SupplierItem::whereIn('id', $allItemIds)->get()->keyBy('id');
                                            $data = $query->get()->map(function($po) use ($supplierItems) {
                                                $itemsList = collect($po->items)->map(function ($item) use ($supplierItems) {
                                                    $name = $supplierItems->get($item['supplier_item_id'])?->nama_item ?? 'N/A';
                                                    $quantity = $item['quantity'] ?? 0;
                                                    $unit = $item['unit'] ?? 'pcs';
                                                    return "• {$name} ({$quantity} {$unit})";
                                                })->join(PHP_EOL);
                                                return [
                                                    'tanggal'     => $po->created_at->format('d-m-Y'),
                                                    'pemasok'     => $po->supplier->name,
                                                    'produk'      => $itemsList,
                                                    'total_harga' => $po->grand_total,
                                                ];
                                            });
                                            return Excel::download(new PurchaseOrdersExport($data), 'laporan-barang-masuk.xlsx');
                                        }
                                    }),
                                
                                FormAction::make('cetakPdf')
                                    ->label('Cetak PDF')
                                    ->button()
                                    ->color('danger')
                                    ->action(function (): StreamedResponse {
                                        $jenisLaporan = $this->data['jenisLaporan'] ?? 'stok';
                                        $records = $this->getFilteredTableQuery()->get();
                                        $data = $this->form->getState();
                                        $viewName = 'reports.' . ($jenisLaporan === 'stok' ? 'stok' : 'barang_masuk');
                                        
                                        $pdf = Pdf::loadView($viewName, compact('records', 'data'));
                                        
                                        return response()->streamDownload(fn() => print($pdf->output()), 'laporan-' . $jenisLaporan . '.pdf');
                                    }),

                                FormAction::make('tampilkan')
                                    ->label('Tampilkan')
                                    ->action('tampilkan'),

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
            'barang_masuk' => PurchaseOrder::query()
                                ->where('status', 'completed')
                                ->with('supplier'),
            default => Product::query(),
        };

        if ($jenisLaporan === 'stok') {
            $tanggal = $this->data['tanggal'] ?? now()->format('Y-m-d');
            $tanggalCarbon = Carbon::parse($tanggal);
            $productId = $this->data['product_id'] ?? null;
            $query->when($productId, fn($q) => $q->where('id', $productId));
            $query->with(['stockMovements' => function ($q) use ($tanggalCarbon) {
                $q->whereDate('created_at', $tanggalCarbon);
            }]);
        } else { 
            $filterPeriode = $this->data['filterPeriode'] ?? 'harian';
            $kolomTanggal = 'created_at';
            
            switch ($filterPeriode) {
                // [4] TAMBAHKAN LOGIKA UNTUK RENTANG TANGGAL
                case 'rentang_tanggal':
                    $tanggalMulai = $this->data['tanggal_mulai'] ?? null;
                    $tanggalAkhir = $this->data['tanggal_akhir'] ?? null;
                    $query->when($tanggalMulai, fn(Builder $q, $date) => $q->whereDate($kolomTanggal, '>=', $date))
                          ->when($tanggalAkhir, fn(Builder $q, $date) => $q->whereDate($kolomTanggal, '<=', $date));
                    break;
                case 'bulanan':
                    $tanggalCarbon = Carbon::parse($this->data['tanggal'] ?? now());
                    $query->whereMonth($kolomTanggal, $tanggalCarbon->month)->whereYear($kolomTanggal, $tanggalCarbon->year);
                    break;
                case 'tahunan':
                    $tanggalCarbon = Carbon::parse($this->data['tanggal'] ?? now());
                    $query->whereYear($kolomTanggal, $tanggalCarbon->year);
                    break;
                default: // Harian
                    $tanggalCarbon = Carbon::parse($this->data['tanggal'] ?? now());
                    $query->whereDate($kolomTanggal, $tanggalCarbon);
                    break;
            }
        }
        return $query;
    }

    protected function getTableColumns(): array
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'stok' => [
                TextColumn::make('tanggal')->label('Tanggal')->state(fn() => Carbon::parse($this->data['tanggal'])->translatedFormat('d M Y')),
                TextColumn::make('name')->label('Nama Produk')->searchable(),
                TextColumn::make('stok_awal')->label('Stok Awal')->state(function (Product $record) {
                    $masukHariIni = $record->stockMovements->where('type', 'in')->sum('quantity');
                    $keluarHariIni = $record->stockMovements->where('type', 'out')->sum('quantity');
                    return ($record->stock - $masukHariIni + $keluarHariIni) . ' ' . $record->unit;
                }),
                TextColumn::make('masuk')->label('Masuk')->state(fn (Product $record) => $record->stockMovements->where('type', 'in')->sum('quantity') . ' ' . $record->unit),
                TextColumn::make('keluar')->label('Keluar')->state(fn (Product $record) => $record->stockMovements->where('type', 'out')->sum('quantity') . ' ' . $record->unit),
                TextColumn::make('stock')->label('Stok Akhir')->state(fn(Product $record) => $record->stock . ' ' . $record->unit)->sortable(),
            ],
            'barang_masuk' => [
                TextColumn::make('created_at')->label('Tanggal')->date('d-m-Y')->sortable(),
                TextColumn::make('supplier.name')->label('Pemasok')->searchable(),
                TextColumn::make('items_list')
                    ->label('Jenis Produk')
                    ->listWithLineBreaks()
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) return [];
                        $itemIds = collect($record->items)->pluck('supplier_item_id')->unique();
                        $supplierItems = \App\Models\SupplierItem::whereIn('id', $itemIds)->get()->keyBy('id');
                        return collect($record->items)->map(fn($item) => $supplierItems->get($item['supplier_item_id'])?->nama_item ?? 'N/A')->all();
                    }),
                TextColumn::make('items_quantity')
                    ->label('Jumlah Produk')
                    ->listWithLineBreaks()
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) return [];
                        return collect($record->items)->map(fn($item) => ($item['quantity'] ?? 0) . ' ' . ($item['unit'] ?? 'pcs'))->all();
                    }),
                TextColumn::make('items_price')
                    ->label('Harga Satuan')
                    ->listWithLineBreaks()
                    ->state(function (PurchaseOrder $record): array {
                        if (empty($record->items)) return [];
                        return collect($record->items)->map(fn($item) => 'Rp ' . number_format($item['price'] ?? 0, 0, ',', '.'))->all();
                    }),
                TextColumn::make('grand_total')
                    ->label('Total Harga')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('Rp ')
                    ->alignEnd()
                    ->summarize(Sum::make()
                        ->label('Total Keseluruhan')
                        ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                        ->money('IDR')),
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