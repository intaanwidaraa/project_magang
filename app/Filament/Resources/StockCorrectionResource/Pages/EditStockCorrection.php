<?php

namespace App\Filament\Resources\StockCorrectionResource\Pages;

use App\Filament\Resources\StockCorrectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockCorrection extends EditRecord
{
    protected static string $resource = StockCorrectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
