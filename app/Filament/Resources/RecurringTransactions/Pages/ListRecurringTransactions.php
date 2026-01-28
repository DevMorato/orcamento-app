<?php

namespace App\Filament\Resources\RecurringTransactions\Pages;

use App\Filament\Resources\RecurringTransactions\RecurringTransactionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListRecurringTransactions extends ListRecords
{
    protected static string $resource = RecurringTransactionResource::class;

    protected static ?string $title = 'Transações Recorrentes';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova Recorrência'),
        ];
    }
}
