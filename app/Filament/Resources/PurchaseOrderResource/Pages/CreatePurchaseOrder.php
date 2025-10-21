<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\SupplierItem;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function afterCreate(): void
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
