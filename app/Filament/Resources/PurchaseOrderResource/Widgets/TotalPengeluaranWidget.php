<?php

namespace App\Filament\Resources\PurchaseOrderResource\Widgets;

use App\Models\PurchaseOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class TotalPengeluaranWidget extends BaseWidget
{
    #[Locked]
    public ?array $filters = null;

    #[On('updateWidgetData')]
    public function updateWidgetData(array $filters): void
    {
        $this->filters = $filters;
    }

    protected function getStats(): array
    {
        $query = PurchaseOrder::query();
        $filters = $this->filters;

        if (!empty($filters['created_at'])) {
            $startDate = $filters['created_at']['created_from'] ?? null;
            $endDate = $filters['created_at']['created_until'] ?? null;

            $query->when($startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $startDate))
                  ->when($endDate, fn (Builder $q) => $q->whereDate('created_at', '<=', $endDate));
        }

        $totalPengeluaran = $query->sum('grand_total');

        return [
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                // Deskripsi sudah dihapus dari sini
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}