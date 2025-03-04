<?php

namespace App\Filament\Resources\CreditResource\Pages;

use App\Filament\Resources\CreditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\CreditStatsWidget;
use App\Filament\Resources\CreditResource\Pages;
use Illuminate\Database\Eloquent\Builder;

class ListCredits extends ListRecords
{
    protected static string $resource = CreditResource::class;

   public function getHeading(): string
{
    return 'Credits List - ' . auth()->user()->name;
}

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Credit Entry')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['type'] = 'credit';
                    $data['user_id'] = auth()->id();
                    return $data;
                }),
        ];
    }

   protected function getTableQuery(): Builder
   {
       return parent::getTableQuery()
           ->where('user_id', auth()->id())
           ->where('type', 'credit');
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
