<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected static ?string $title = 'Editar Conta';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
