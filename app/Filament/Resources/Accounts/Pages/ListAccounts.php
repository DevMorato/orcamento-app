<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected static ?string $title = 'Contas Bancárias';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova Conta'),
        ];
    }
}
