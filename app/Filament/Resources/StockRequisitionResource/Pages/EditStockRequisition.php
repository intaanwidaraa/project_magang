<?php

namespace App\Filament\Resources\StockRequisitionResource\Pages;

use App\Filament\Resources\StockRequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockRequisition extends EditRecord
{
    protected static string $resource = StockRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
