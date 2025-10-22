<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockCorrectionResource\Pages;
use App\Models\StockCorrection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;

class StockCorrectionResource extends Resource
{
    protected static ?string $model = StockCorrection::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Log Koreksi Stok';
    protected static ?string $navigationGroup = 'Manajemen Stok';

    // --- SEMBUNYIKAN DARI SIDEBAR ---
    protected static bool $shouldRegisterNavigation = false;

    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stock_requisition_id')->label('ID Permintaan'),
                Forms\Components\TextInput::make('user.name')->label('Dikoreksi Oleh'),
                Forms\Components\TextInput::make('product_name_cache')->label('Nama Barang'),
                Forms\Components\TextInput::make('quantity_before')->label('Jumlah Tercatat (Sebelum)'),
                Forms\Components\TextInput::make('quantity_after')->label('Jumlah Seharusnya (Sesudah)'),
                Forms\Components\TextInput::make('difference')->label('Selisih'),
                Forms\Components\Textarea::make('reason')->label('Alasan Koreksi'),
                Forms\Components\DateTimePicker::make('created_at')->label('Waktu Koreksi'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Koreksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stockRequisition.requester_name') 
                    ->label('Peminta Asli')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name_cache')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('difference')
                    ->label('Selisih')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dikoreksi Oleh')
                    ->searchable(),
            ])
            ->filters([
                Filter::make('stock_requisition_id')
                    ->query(fn (Builder $query, array $data): Builder => $query->where('stock_requisition_id', $data['value']))
                    ->label('ID Permintaan')
                    ->hidden()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockCorrections::route('/'),
            'view' => Pages\ViewStockCorrection::route('/{record}'),
        ];
    }    
}