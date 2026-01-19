<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['family_id'] = auth()->user()->family_id;
        $data['user_id'] = auth()->id();

        if ($data['type'] === 'income') {
            $data['splits'] = [];
        } elseif (isset($data['splits']) && is_array($data['splits']) && isset($data['amount'])) {
            $totalAmount = $data['amount'];
            foreach ($data['splits'] as $key => $split) {
                if (isset($split['percentage'])) {
                    $data['splits'][$key]['amount'] = round($totalAmount * ($split['percentage'] / 100), 2);
                }
            }
            // Optional: Adjust last split to fix rounding errors if needed, but keeping it simple for now.
        }

        return $data;
    }
}
