<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Barang Masuk (PO)';
    protected static ?string $modelLabel = 'Barang Masuk (PO)';
    protected static ?string $pluralModelLabel = 'Barang Masuk (PO)';
    protected static ?string $navigationGroup = 'Manajemen Stok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('po_number')
                    ->label('Nomor PO')
                    ->default('PO-' . random_int(1000, 9999))
                    ->required(),
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('phone_number'),
                    ])
                    ->required(),
                Forms\Components\Repeater::make('items')
                    ->label('Daftar Barang')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Barang')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(1),
                    ])
                    ->columns(2)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')->label('Nomor PO')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Pemasok')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                    'ordered' => 'warning',
                    'completed' => 'success',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->items as $item) {
                                $product = Product::find($item['product_id']);
                                // Logika inti: Menambah stok produk
                                $product->increment('stock', $item['quantity']);
                                StockMovement::create([
                                    'product_id' => $item['product_id'],
                                    'type' => 'in',
                                    'quantity' => $item['quantity'],
                                    'reference_type' => PurchaseOrder::class,
                                    'reference_id' => $record->id,
                                ]);
                            }
                            $record->update(['status' => 'completed']);
                        });
                        Notification::make()->title('Barang diterima, stok telah diupdate!')->success()->send();
                    })
                    ->visible(fn(PurchaseOrder $record): bool => $record->status === 'ordered'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
