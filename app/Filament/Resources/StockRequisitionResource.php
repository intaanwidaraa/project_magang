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
use Illuminate\Support\Str;
use App\Models\StockCorrection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\StockCorrectionResource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class StockRequisitionResource extends Resource
{
    protected static ?string $model = StockRequisition::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';
    protected static ?string $navigationLabel = 'Barang Keluar';
    protected static ?string $modelLabel = 'Barang Keluar';
    protected static ?string $pluralModelLabel = 'Barang Keluar';
    protected static ?string $navigationGroup = 'Manajemen Stok';
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('corrections'); 
    }

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
                                Forms\Components\DatePicker::make('created_at')
                                    ->label('Tanggal Pengambilan')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\Select::make('department')
                                    ->label('Bagian Pengambil')
                                    ->options([
                                        'mekanik' => 'Mekanik',
                                        'logistik' => 'Logistik',
                                        'engineering' => 'Engineering',
                                        'utility' => 'Utility',
                                        'workshop' => 'Workshop',
                                        'packing' => 'Packing',
                                        'admin' => 'Admin',
                                        'lainnya' => 'Lainnya',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('shift')
                                    ->label('Shift')
                                    ->options([
                                        '1' => 'Shift 1',
                                        '2' => 'Shift 2',
                                        '3' => 'Shift 3',
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
                                                    $set('product_name', $product->name);
                                                    $set('product_unit', $product->unit ?? 'pcs'); 
                                                   
                                                } else {
                                                    $set('sku', null);
                                                    $set('product_name', null);
                                                    $set('product_unit', null);
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

                                            
                                        Forms\Components\Hidden::make('product_name')
                                            ->dehydrated(),
                                        Forms\Components\Hidden::make('product_unit')
                                            ->dehydrated(),
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
            ])
            ;}
    
    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('requester_name')->label('Nama Pengambil')->searchable(),
            Tables\Columns\TextColumn::make('department')->label('Bagian')->sortable(),
            Tables\Columns\TextColumn::make('shift')
                ->label('Shift')
                ->formatStateUsing(fn (?string $state): string => $state ? "Shift {$state}" : '-')
                ->sortable()
                ->toggleable(), 
            Tables\Columns\TextColumn::make('notes')->label('Keterangan')->limit(30)->toggleable(),
            Tables\Columns\TextColumn::make('items')
                ->label('Daftar Barang')
                ->listWithLineBreaks()
                ->limitList(3)
                ->state(function (StockRequisition $record): array {
                    $items = $record->items;
                    if (is_string($items)) {
                        $items = json_decode($items, true);
                    } else {
                        $items = json_decode(json_encode($items), true); 
                    }
                    if (empty($items) || !is_array($items)) {
                        return [];
                    }
                    $items = collect($items);
                    $oldItemProductIds = $items
                        ->filter(fn ($item) => !isset($item['product_name']) && isset($item['product_id']))
                        ->pluck('product_id')
                        ->unique()
                        ->filter();
                    $products = collect();
                    if ($oldItemProductIds->isNotEmpty()) {
                        $products = \App\Models\Product::whereIn('id', $oldItemProductIds)
                            ->get()
                            ->keyBy('id');
                    }
                    return $items->map(function ($item) use ($products) {
                        $quantity = $item['quantity'] ?? 0;
                        $productName = 'Barang Dihapus';
                        $unit = 'pcs';
                        if (isset($item['product_name'])) {
                            $productName = $item['product_name'];
                            $unit = $item['product_unit'] ?? 'pcs';
                        } 
                        else if (isset($item['product_id'])) {
                            $product = $products->get($item['product_id']);
                            if ($product) {
                                $productName = $product->name;
                                $unit = $product->unit ?? 'pcs';
                            }
                        }
                        $limitedName = Str::limit($productName, 40, '...'); 
                        return "{$limitedName} ({$quantity} {$unit})";
                    })->all();
                })
                ->toggleable(),

            Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                'pending' => 'warning',
                'completed' => 'success',
            }),
            Tables\Columns\IconColumn::make('corrections_count') 
                ->label('') 
                ->boolean()
                ->state(fn (StockRequisition $record): bool => $record->corrections_count > 0) 
                ->trueIcon('heroicon-o-exclamation-triangle') 
                ->trueColor('danger')
                ->falseIcon(null) 
                ->tooltip(fn (StockRequisition $record): ?string => 
                $record->corrections_count > 0 ? 'Data ini pernah dikoreksi' : null 
                ),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal Dibuat')
                ->date('d M Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Waktu Dikeluarkan')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->formatStateUsing(function ($state, $record) {
                    if ($record->status === 'pending') {
                        return '-';
                    }
                    return $state ? $state->format('d M Y H:i') : '-';
                }),
        ])
        ->filters([
                SelectFilter::make('dikoreksi')
                    ->label('Status Koreksi')
                    ->options([
                        'ya' => 'Pernah Dikoreksi',
                        'tidak' => 'Belum Dikoreksi',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'];
                        if ($value === 'ya') {
                            return $query->has('corrections'); 
                        } elseif ($value === 'tidak') {
                            return $query->doesntHave('corrections');
                        }
                        return $query; 
                    })
            ])
        ->headerActions([
            Tables\Actions\Action::make('lihat_semua_koreksi')
                ->label('Lihat Semua Log Koreksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->url(StockCorrectionResource::getUrl('index')) 
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()
                ->visible(fn(StockRequisition $record): bool => $record->status === 'pending'),
            Tables\Actions\Action::make('complete')
                ->label('Keluarkan Barang')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (StockRequisition $record) {
                    DB::transaction(function () use ($record) {
                        $itemsToProcess = $record->items;
                        if (is_string($itemsToProcess)) {
                            $itemsToProcess = json_decode($itemsToProcess, true);
                        } else {
                            $itemsToProcess = json_decode(json_encode($itemsToProcess), true); 
                        }
                        foreach ($itemsToProcess as $item) {
                            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                                continue;
                            }
                            $product = Product::find($item['product_id']);
                            if (!$product) {
                                 Notification::make()->title("Produk tidak ditemukan!")->danger()->send();
                                 throw new \Exception('Produk tidak ditemukan.');
                            }
                            if ($product->stock < $item['quantity']) {
                                Notification::make()->title("Stok {$product->name} tidak cukup!")->danger()->send();
                                throw new \Exception('Stok tidak cukup.');
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
            Tables\Actions\Action::make('koreksi_stok')
                ->label('Koreksi Stok')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('danger')
                ->visible(fn (StockRequisition $record): bool => $record->status === 'completed')
                ->form(function (StockRequisition $record) {
                    $items = json_decode(json_encode($record->items), true);
                    $options = collect($items)->mapWithKeys(function ($item) {
                        $productName = $item['product_name'] ?? 'Barang Dihapus';
                        if ($productName === 'Barang Dihapus' && isset($item['product_id'])) {
                            $product = \App\Models\Product::find($item['product_id']);
                            if ($product) $productName = $product->name;
                        }
                        $quantity = $item['quantity'] ?? '?';
                        $unit = $item['product_unit'] ?? 'pcs';
                        return [$item['product_id'] => "{$productName} (Tercatat: {$quantity} {$unit})"];
                    });
                    return [
                        Select::make('product_id')
                            ->label('Barang yang Akan Dikoreksi')
                            ->options($options)
                            ->required(),
                        TextInput::make('quantity_after')
                            ->label('Jumlah Seharusnya (Yang Benar)')
                            ->numeric()
                            ->required()
                            ->helperText('Masukkan jumlah yang *sebenarnya* diambil teknisi.'),
                        Textarea::make('reason')
                            ->label('Alasan Koreksi')
                            ->required()
                            ->placeholder('Contoh: Salah input, teknisi hanya ambil 5 pcs'),
                    ];
                })
                ->action(function (array $data, StockRequisition $record) {
                    DB::transaction(function () use ($data, $record) {
                        $items = json_decode(json_encode($record->items), true);
                        if (!is_array($items)) {
                            Notification::make()->title('Gagal memproses data barang!')->danger()->send(); return;
                        }

                        $productIdToCorrect = (int)$data['product_id'];
                        
                        $itemIndexToCorrect = collect($items)->search(function ($item) use ($productIdToCorrect) {
                            return isset($item['product_id']) && (int)$item['product_id'] === $productIdToCorrect;
                        });

                        if ($itemIndexToCorrect === false) {
                            Notification::make()->title('Gagal menemukan barang dalam daftar permintaan!')->danger()->send(); return;
                        }
                        
                        $itemToCorrect = $items[$itemIndexToCorrect]; 

                        $product = Product::find($productIdToCorrect);
                        if (!$product) {
                            Notification::make()->title('Produk tidak ditemukan di database!')->danger()->send(); return;
                        }

                        if (!isset($itemToCorrect['quantity'])) {
                             Notification::make()->title('Data jumlah barang asli tidak valid!')->danger()->send(); return;
                        }

                        $quantity_before = (int) $itemToCorrect['quantity'];
                        $quantity_after = (int) $data['quantity_after'];
                        $stockAdjustment = $quantity_before - $quantity_after;

                        if ($stockAdjustment > 0) {
                            $product->increment('stock', $stockAdjustment);
                            $movementType = 'correction-in';
                        } else if ($stockAdjustment < 0) {
                            if ($product->stock < abs($stockAdjustment)) {
                                 Notification::make()->title("Stok {$product->name} tidak cukup untuk koreksi pengurangan!")->danger()->send();
                                 throw new \Exception('Stok tidak cukup untuk koreksi pengurangan.'); 
                            }
                            $product->decrement('stock', abs($stockAdjustment));
                            $movementType = 'correction-out';
                        } else {
                            Notification::make()->title('Jumlah sama. Tidak ada koreksi stok.')->info()->send(); return;
                        }

                        StockMovement::create([
                            'product_id' => $product->id,
                            'type' => $movementType,
                            'quantity' => abs($stockAdjustment),
                            'reference_type' => StockRequisition::class,
                            'reference_id' => $record->id,
                        ]);

                        StockCorrection::create([
                            'user_id' => Auth::id(),
                            'stock_requisition_id' => $record->id,
                            'product_id' => $product->id,
                            'product_name_cache' => $product->name,
                            'quantity_before' => $quantity_before,
                            'quantity_after' => $quantity_after,
                            'difference' => $quantity_after - $quantity_before,
                            'reason' => $data['reason'],
                        ]);

                        $updatedItems = $items; 
                        $updatedItems[$itemIndexToCorrect]['quantity'] = $quantity_after;
                        $record->update(['items' => $updatedItems]);
                       

                    });

                    Notification::make()->title('Stok berhasil dikoreksi dan data permintaan diperbarui!')->success()->send();
                }),
            
            Tables\Actions\Action::make('riwayat_koreksi')
                ->label('Riwayat Koreksi')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->visible(fn (StockRequisition $record): bool => $record->status === 'completed')
                ->url(fn (StockRequisition $record): string => 
                    StockCorrectionResource::getUrl('index', [
                        'tableFilters' => [
                            'stock_requisition_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])
                ),
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
