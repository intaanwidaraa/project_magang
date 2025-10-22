<?php

namespace App\Filament\Resources\StockCorrectionResource\Pages;

use App\Filament\Resources\StockCorrectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockCorrection extends ViewRecord
{
    protected static string $resource = StockCorrectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
