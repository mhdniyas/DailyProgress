<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Hidden;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Stock Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(auth()->id()),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\Select::make('type')
                            ->options(Item::getTypes())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('category')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\Select::make('unit')
                            ->options(Item::getUnits())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(999999.99)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stock Settings')
                    ->schema([
                        Forms\Components\TextInput::make('minimum_stock')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('maximum_stock')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('supplier')
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(Item::getTypes()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            ->defaultSort('name')
            ->poll('60s'); // Refresh every 60 seconds
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
