<?php

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Resources\Budgets\BudgetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;

    protected static ?string $title = 'Orçamentos';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo Orçamento'),
        ];
    }
}
