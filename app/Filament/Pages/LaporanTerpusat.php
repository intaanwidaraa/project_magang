<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\PurchaseOrder;
use App\Exports\ProductsExport;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PurchaseOrdersExport;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;


class LaporanTerpusat extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static string $view = 'filament.pages.laporan-terpusat';

    // Pengaturan sidebar
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Terpusat';
    protected static ?string $title = 'Laporan Terpusat';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['jenisLaporan' => 'stok']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('jenisLaporan')
                    ->label('Pilih Jenis Laporan')
                    ->options([
                        'stok' => 'Laporan Stok Saat Ini',
                        'barang_masuk' => 'Laporan Barang Masuk (PO)',
                    ])
                    ->live()
                    ->default('stok'),
            ])->statePath('data');
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'stok' => Product::query(),
            'barang_masuk' => PurchaseOrder::query(),
            default => Product::query(),
        };
    }

    protected function getTableColumns(): array
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'stok' => [
                TextColumn::make('name')->label('Nama Produk')->searchable(),
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('stock')->label('Stok Saat Ini')->sortable(),
            ],
            'barang_masuk' => [
                TextColumn::make('po_number')->label('Nomor PO')->searchable(),
                TextColumn::make('supplier.name')->label('Pemasok')->searchable(),
                TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                    'ordered' => 'warning',
                    'completed' => 'success',
                }),
                TextColumn::make('created_at')->label('Tanggal Dibuat')->dateTime()->sortable(),
            ],
            default => [],
        };
    }

    protected function getTableFilters(): array
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'barang_masuk' => [
                Filter::make('supplier')
                    ->form([
                        Select::make('supplier_id')
                            ->label('Pemasok')
                            ->options(Supplier::query()->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['supplier_id'] ?? null,
                            fn(Builder $query, $supplierId): Builder => $query->where('supplier_id', '=', $supplierId)
                        );
                    }),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null, // <-- Tambahkan '?? null'
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null, // <-- Tambahkan '?? null'
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ],
            default => [],
        };
    }

    protected function getTableHeaderActions(): array
    {
        return match ($this->data['jenisLaporan'] ?? 'stok') {
            'stok' => [
                Action::make('exportStok')->label('Ekspor ke Excel')->action(fn() => Excel::download(new ProductsExport, 'laporan-stok.xlsx')),
            ],
            'barang_masuk' => [
                Action::make('exportBarangMasuk')->label('Ekspor ke Excel')->action(function () {
                    $data = $this->getFilteredTableQuery()->get()->map(fn($po) => ['po_number' => $po->po_number, 'supplier' => $po->supplier->name, 'status' => $po->status, 'created_at' => $po->created_at->format('Y-m-d H:i:s')]);
                    return Excel::download(new PurchaseOrdersExport($data), 'laporan-barang-masuk.xlsx');
                }),
            ],
            default => [],
        };
    }
}
