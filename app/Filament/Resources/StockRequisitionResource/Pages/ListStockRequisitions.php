<?php

namespace App\Filament\Resources\StockRequisitionResource\Pages;

use App\Filament\Resources\StockRequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockRequisitions extends ListRecords
{
    protected static string $resource = StockRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
