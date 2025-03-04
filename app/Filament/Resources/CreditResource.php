<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Transaction;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\CreditResource\Pages;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;

class CreditResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationLabel = 'Money In (Credit)';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down';
    protected static ?string $navigationGroup = 'Money Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->where('type', 'credit');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->id()),

                Forms\Components\Hidden::make('type')
                    ->default('credit'),

                Select::make('category_id')
                    ->label('Category')
                    ->options(function () {
                        $income = Category::where('name', 'Income')->first();
                        if (!$income) return [];

                        $options = [];
                        $categories = Category::whereNotNull('parent_id')
                            ->with('parent')
                            ->get();

                        foreach ($categories as $category) {
                            // Check if category is under Income
                            $parent = $category;
                            $isIncomeCategory = false;
                            while ($parent->parent_id) {
                                if ($parent->parent_id === $income->id) {
                                    $isIncomeCategory = true;
                                    break;
                                }
                                $parent = $parent->parent;
                            }

                            if ($isIncomeCategory) {
                                $options[$category->id] = $category->full_path;
                            }
                        }

                        asort($options); // Sort options alphabetically
                        return $options;
                    })
                    ->searchable()
                    ->required()
                    ->preload(),

                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->rules(['required', 'numeric', 'min:0']),

                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->default(now()),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->maxLength(255),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('category.full_path')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('amount')
                    ->money('inr')
                    ->sortable(),

                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(30)
                    ->tooltip(function (Transaction $record): string {
                        return $record->description ?? '';
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query) => $query->whereDate('date', '>=', $data['from'])
                            )
                            ->when(
                                $data['until'],
                                fn ($query) => $query->whereDate('date', '<=', $data['until'])
                            );
                    }),
            ])
            ->actions([
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
            'index' => Pages\ListCredits::route('/'),
            'create' => Pages\CreateCredit::route('/create'),
            'edit' => Pages\EditCredit::route('/{record}/edit'),
        ];
    }
}
