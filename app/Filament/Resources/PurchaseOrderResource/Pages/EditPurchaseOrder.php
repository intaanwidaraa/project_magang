<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\SupplierItem; 

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        
        $items = $this->record->items; 
        
        if (is_array($items)) {
            foreach ($items as $item) {
                $supplierItemId = $item['supplier_item_id'] ?? null;
                $newPrice = $item['price'] ?? null;
                
                if ($supplierItemId && $newPrice !== null) {
                    $supplierItem = SupplierItem::find($supplierItemId);
                    
                    if ($supplierItem && $supplierItem->harga != $newPrice) {
                        $supplierItem->harga = $newPrice;
                        $supplierItem->save();
                    }
                }
            }
        }
    }    
}
