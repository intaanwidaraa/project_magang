<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockRequisitionResource\Pages;
use App\Models\Product;
use App\Models\StockRequisition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;

class StockRequisitionResource extends Resource
{
    protected static ?string $model = StockRequisition::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';

    protected static ?string $navigationLabel = 'Barang Keluar';
    protected static ?string $modelLabel = 'Barang Keluar';
    protected static ?string $pluralModelLabel = 'Barang Keluar';
    protected static ?string $navigationGroup = 'Manajemen Stok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Forms\Components\Grid::make()->columns(3)->schema([

                    Forms\Components\Group::make()->schema([
                        Forms\Components\Section::make('Informasi Pengambilan')
                            ->schema([
                                Forms\Components\TextInput::make('requester_name')
                                    ->label('Nama Pengambil')
                                    ->required(),
                                Forms\Components\Select::make('department')
                                    ->label('Bagian Pengambil')
                                    ->options([
                                        'mekanik' => 'Mekanik',
                                        'logistik' => 'Logistik',
                                        'lainnya' => 'Lainnya',

                                    ])
                                    ->required(),
                            ])->columns(2), 

                        Forms\Components\Section::make('Daftar Barang')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->label(false) 
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Barang')
                                            ->options(Product::query()->pluck('name', 'id'))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('sku', $product->sku);
                                                }
                                            })
                                            ->searchable()
                                            ->columnSpan([
                                                'md' => 4, 
                                            ]),

                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan([
                                                'md' => 2,
                                            ]),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->columnSpan([
                                                'md' => 2,
                                            ]),
                                    ])
                                    ->columns([ 
                                        'md' => 8,
                                    ])
                                    ->required(),
                            ]),
                    ])->columnSpan(2), 

                    Forms\Components\Group::make()->schema([
                        Forms\Components\Section::make('Keterangan')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label(false) 
                                    ->placeholder('Catatan tambahan (opsional)...')
                                    ->rows(8),
                            ]),
                    ])->columnSpan(1), 
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requester_name')->label('Nama Pengambil')->searchable(),
                Tables\Columns\TextColumn::make('department')->label('Bagian')->sortable(),
                Tables\Columns\TextColumn::make('notes')->label('Keterangan')->limit(30)->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                    'pending' => 'warning',
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
                    ->label('Keluarkan Barang')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (StockRequisition $record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->items as $item) {
                                $product = Product::find($item['product_id']);
                                if ($product->stock < $item['quantity']) {
                                    Notification::make()->title("Stok {$product->name} tidak cukup!")->danger()->send();
                                    throw new \Exception('Stok tidak cukup.');
                                }

                                
                                if (is_null($product->tanggal_mulai_pemakaian)) {
                                    $product->update([
                                        'tanggal_mulai_pemakaian' => now(),
                                    ]);
                                }

                                $product->decrement('stock', $item['quantity']);

                                StockMovement::create([
                                    'product_id' => $item['product_id'],
                                    'type' => 'out',
                                    'quantity' => $item['quantity'],
                                    'reference_type' => StockRequisition::class,
                                    'reference_id' => $record->id,
                                ]);

                                if ($product->stock <= $product->minimum_stock) {
                                    Notification::make()
                                        ->title("Stok Kritis: {$product->name}")
                                        ->body("Stok saat ini ({$product->stock}) telah mencapai atau di bawah batas minimum ({$product->minimum_stock}). Segera lakukan pemesanan ulang.")
                                        ->warning()
                                        ->persistent()
                                        ->send();
                                }
                            }
                            $record->update(['status' => 'completed']);
                        });
                        Notification::make()->title('Barang berhasil dikeluarkan!')->success()->send();
                    })
                    ->visible(fn(StockRequisition $record): bool => $record->status === 'pending'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockRequisitions::route('/'),
            'create' => Pages\CreateStockRequisition::route('/create'),
            'view' => Pages\ViewStockRequisition::route('/{record}'),
            'edit' => Pages\EditStockRequisition::route('/{record}/edit'),
        ];
    }
}
