<?php

namespace App\Filament\Resources\CreditResource\Pages;

use App\Filament\Resources\CreditResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCredit extends CreateRecord
{
    protected static string $resource = CreditResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'credit';
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Credit entry created successfully';
    }
}
