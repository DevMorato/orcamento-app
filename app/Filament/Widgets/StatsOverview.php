<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 4;

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
        // Usar request como fonte primária, session como fallback
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');
        $user = Auth::user();

        // Obter mês/ano do filtro
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));

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
        $monthStart = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $monthEnd = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();

        // Nome do mês
        $monthName = $monthStart->locale('pt_BR')->monthName;

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

        // Comparativo com mês anterior
        $prevMonthStart = $monthStart->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $monthStart->copy()->subMonth()->endOfMonth();

        $prevIncome = (clone $transactionQuery)
            ->where('type', 'income')
            ->whereBetween('date', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');

        if ($scope === 'family') {
            $prevExpenses = Transaction::query()
                ->where('family_id', $user->family_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$prevMonthStart, $prevMonthEnd])
                ->sum('amount');
        } else {
            $prevExpenses = $user->transactionSplits()
                ->whereHas('transaction', function ($q) use ($prevMonthStart, $prevMonthEnd) {
                    $q->whereBetween('date', [$prevMonthStart, $prevMonthEnd]);
                })
                ->sum('amount');
        }

        // Cálculo de variação
        $incomeChange = $prevIncome > 0 ? (($income - $prevIncome) / $prevIncome) * 100 : 0;
        $expensesChange = $prevExpenses > 0 ? (($expenses - $prevExpenses) / $prevExpenses) * 100 : 0;

        return [
            Stat::make('Saldo Atual', 'R$ ' . number_format($balance, 2, ',', '.'))
                ->description('Saldo acumulado')
                ->color($balance >= 0 ? 'success' : 'danger'),

            Stat::make('Receitas', 'R$ ' . number_format($income, 2, ',', '.'))
                ->description($this->formatChange($incomeChange) . ' vs mês anterior')
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success'),

            Stat::make('Despesas', 'R$ ' . number_format($expenses, 2, ',', '.'))
                ->description($this->formatChange($expensesChange) . ' vs mês anterior')
                ->descriptionIcon($expensesChange <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color('danger'),

            Stat::make('Economia', 'R$ ' . number_format($savings, 2, ',', '.'))
                ->description('Rec. - Desp. em ' . $monthName)
                ->color($savings >= 0 ? 'success' : 'danger'),
        ];
    }

    private function formatChange(float $change): string
    {
        $sign = $change >= 0 ? '+' : '';
        return $sign . number_format($change, 1, ',', '.') . '%';
    }
}
