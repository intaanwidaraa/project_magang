<?php

namespace App\Filament\Resources\StockRequisitionResource\Pages;

use App\Filament\Resources\StockRequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockRequisition extends ViewRecord
{
    protected static string $resource = StockRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
