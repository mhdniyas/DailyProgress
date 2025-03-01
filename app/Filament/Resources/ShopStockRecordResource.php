<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopStockRecordResource\Pages;
use App\Models\ShopStockRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ShopStockRecordResource extends Resource
{
    protected static ?string $model = ShopStockRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Shop Stock Records';

    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $modelLabel = 'Shop Stock Record';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock Details')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->relationship('item', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('number_of_bags')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('average_quantity')) {
                                    $set('total_quantity', floatval($state) * floatval($get('average_quantity')));
                                }
                            })
                            ->label('Number of Bags'),

                        Forms\Components\TextInput::make('average_quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('number_of_bags')) {
                                    $set('total_quantity', floatval($state) * floatval($get('number_of_bags')));
                                }
                            })
                            ->label('Average Quantity per Bag'),

                        Forms\Components\TextInput::make('total_quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(true) // This is important
                            ->label('Total Quantity'),

                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->required()
                            ->default(now())
                            ->label('Record Date'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->sortable()
                    ->searchable()
                    ->label('Item'),

                Tables\Columns\TextColumn::make('number_of_bags')
                    ->sortable()
                    ->label('Bags'),

                Tables\Columns\TextColumn::make('average_quantity')
                    ->sortable()
                    ->label('Avg Qty'),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->sortable()
                    ->label('Total Qty'),

                Tables\Columns\TextColumn::make('recorded_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Recorded Date'),
            ])
            ->filters([
                SelectFilter::make('item_id')
                    ->relationship('item', 'name')
                    ->label('Item'),

                Filter::make('recorded_at')
                    ->form([
                        Forms\Components\DatePicker::make('recorded_from')
                            ->label('Recorded From'),
                        Forms\Components\DatePicker::make('recorded_until')
                            ->label('Recorded Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['recorded_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('recorded_at', '>=', $date),
                            )
                            ->when(
                                $data['recorded_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('recorded_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShopStockRecords::route('/'),
            'create' => Pages\CreateShopStockRecord::route('/create'),
            'view' => Pages\ViewShopStockRecord::route('/{record}'),
            'edit' => Pages\EditShopStockRecord::route('/{record}/edit'),
        ];
    }
}
