<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    #[\Livewire\Attributes\Url]
    public ?string $scope = 'user';

    public function mount(): void
    {
        $this->scope = request()->query('scope', 'user');
        if (method_exists(parent::class, 'mount')) {
            parent::mount();
        }
    }

    protected function getStats(): array
    {
        $scope = $this->scope;
        $user = Auth::user();

        // Query Base
        $transactionQuery = Transaction::query();

        if ($scope === 'family') {
            $transactionQuery->where('family_id', $user->family_id);
            // Saldo da Família: Soma de todos os saldos dos usuários da família
            $balance = $user->family->users->sum(fn($u) => $u->getCurrentBalance());
        } else {
            $transactionQuery->where('user_id', $user->id);
            $balance = $user->getCurrentBalance();
        }

        // Filters for Month
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // Income (Receitas)
        $income = (clone $transactionQuery)
            ->where('type', 'income')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');

        // Expenses (Despesas) - This is tricky for individual scope because of splits
        if ($scope === 'family') {
            $expenses = (clone $transactionQuery)
                ->where('type', 'expense')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
        } else {
            // My Expenses = My Splits
            $expenses = $user->transactionSplits()
                ->whereHas('transaction', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('date', [$monthStart, $monthEnd]);
                })
                ->sum('amount');
        }

        $savings = $income - $expenses;

        return [
            Stat::make('Saldo Atual', 'R$ ' . number_format($balance, 2, ',', '.'))
                ->description('Saldo acumulado')
                ->color($balance >= 0 ? 'success' : 'danger'),

            Stat::make('Receitas (Mês)', 'R$ ' . number_format($income, 2, ',', '.'))
                ->description('Entradas em ' . now()->locale('pt_BR')->monthName)
                ->color('success'),

            Stat::make('Despesas (Mês)', 'R$ ' . number_format($expenses, 2, ',', '.'))
                ->description('Saídas em ' . now()->locale('pt_BR')->monthName)
                ->color('danger'),

            Stat::make('Economia (Mês)', 'R$ ' . number_format($savings, 2, ',', '.'))
                ->description('Rec. - Desp.')
                ->color($savings >= 0 ? 'success' : 'danger'),
        ];
    }
}
