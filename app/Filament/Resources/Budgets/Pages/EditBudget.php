<?php

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Resources\Budgets\BudgetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected static ?string $title = 'Editar Orçamento';

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
