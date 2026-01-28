<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AccountBalancesWidget extends Widget
{
    protected string $view = 'filament.widgets.account-balances-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function getAccounts(): array
    {
        $user = Auth::user();

        return Account::query()
            ->where('family_id', $user->family_id)
            ->where('is_active', true)
            ->get()
            ->map(fn($account) => [
                'name' => $account->name,
                'type' => $account->getTypeLabel(),
                'balance' => $account->getCurrentBalance(),
                'color' => $account->color,
            ])
            ->toArray();
    }

    public function getTotalBalance(): float
    {
        $accounts = $this->getAccounts();
        return array_sum(array_column($accounts, 'balance'));
    }
}
