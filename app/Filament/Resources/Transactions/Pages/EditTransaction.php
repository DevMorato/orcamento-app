<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $title = 'Editar Transação';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If changing to income, we should remove any splits
        if ($data['type'] === 'income') {
            $data['splits'] = [];
            // We also need to actually delete records from DB if they exist, 
            // but Filament's repeater relationship handling might needs an empty array to know "delete these".
            // However, simply passing empty array for a HasMany might not prevent them from being orphaned if not handled correctly.
            // Safe bet: manually delete them if we are sure.
            $this->record->splits()->delete();
        } elseif (isset($data['splits']) && is_array($data['splits']) && isset($data['amount'])) {
            $totalAmount = $data['amount'];
            foreach ($data['splits'] as $key => $split) {
                if (isset($split['percentage'])) {
                    $data['splits'][$key]['amount'] = round($totalAmount * ($split['percentage'] / 100), 2);
                }
            }
        }

        return $data;
    }
}
