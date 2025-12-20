<?php

namespace App\Filament\Resources\SubscriptionRequestResource\Pages;

use App\Filament\Resources\SubscriptionRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionRequest extends EditRecord
{
    protected static string $resource = SubscriptionRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
