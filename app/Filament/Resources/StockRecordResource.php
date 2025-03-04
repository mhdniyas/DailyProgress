<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockRecordResource\Pages;
use App\Models\StockRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Hidden;

class StockRecordResource extends Resource
{
    protected static ?string $model = StockRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Stock Records';

    protected static ?string $modelLabel = 'Stock Record';

    protected static ?string $navigationGroup = 'Stock Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                            ->default(auth()->id()),

                Forms\Components\Section::make('Stock Details')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->relationship('item', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('total_quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('average_quantity')) {
                                    $bags = floatval($state) / floatval($get('average_quantity'));
                                    $set('number_of_bags', round($bags, 2));
                                }
                            })
                            ->label('Total Quantity'),

                        Forms\Components\TextInput::make('average_quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('total_quantity')) {
                                    $bags = floatval($get('total_quantity')) / floatval($state);
                                    $set('number_of_bags', round($bags, 2));
                                }
                            })
                            ->label('Average Quantity per Bag'),

                        Forms\Components\TextInput::make('number_of_bags')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(true)
                            ->label('Number of Bags'),

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

                Tables\Columns\TextColumn::make('total_quantity')
                    ->numeric(
                        decimalPlaces: 2,
                        thousandsSeparator: ',',
                    )
                    ->sortable()
                    ->label('Total Qty'),

                Tables\Columns\TextColumn::make('average_quantity')
                    ->numeric(
                        decimalPlaces: 2,
                        thousandsSeparator: ',',
                    )
                    ->sortable()
                    ->label('Avg Qty'),

                Tables\Columns\TextColumn::make('number_of_bags')
                    ->numeric(
                        decimalPlaces: 2,
                        thousandsSeparator: ',',
                    )
                    ->sortable()
                    ->label('Bags'),

                Tables\Columns\TextColumn::make('recorded_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('item_id')
                    ->relationship('item', 'name')
                    ->label('Item'),

                Filter::make('recorded_at')
                    ->form([
                        Forms\Components\DatePicker::make('recorded_from'),
                        Forms\Components\DatePicker::make('recorded_until'),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockRecords::route('/'),
            'create' => Pages\CreateStockRecord::route('/create'),
            'view' => Pages\ViewStockRecord::route('/{record}'),
            'edit' => Pages\EditStockRecord::route('/{record}/edit'),
        ];
    }
}
