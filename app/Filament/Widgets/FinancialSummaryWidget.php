<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinancialSummaryWidget extends Widget
{
    protected string $view = 'filament.widgets.financial-summary-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public function getSummary(): array
    {
        $user = Auth::user();

        // Obter mês/ano do filtro
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');

        $monthStart = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $monthEnd = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        $monthName = $monthStart->locale('pt_BR')->monthName;

        // Buscar orçamentos
        $budgets = Budget::query()
            ->where('family_id', $user->family_id)
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->get();

        $totalBudgeted = $budgets->sum('amount');

        // Calcular gastos
        if ($scope === 'family') {
            $totalSpent = Transaction::query()
                ->where('family_id', $user->family_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $totalIncome = Transaction::query()
                ->where('family_id', $user->family_id)
                ->where('type', 'income')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
        } else {
            $totalSpent = $user->transactionSplits()
                ->whereHas('transaction', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('date', [$monthStart, $monthEnd]);
                })
                ->sum('amount');

            $totalIncome = Transaction::query()
                ->where('user_id', $user->id)
                ->where('type', 'income')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
        }

        // Contar alertas
        $alertCount = 0;
        $exceededCount = 0;

        foreach ($budgets as $budget) {
            $percentage = $budget->getPercentageUsed();
            if ($percentage >= 100) {
                $exceededCount++;
            } elseif ($percentage >= 80) {
                $alertCount++;
            }
        }

        $remaining = $totalBudgeted > 0 ? $totalBudgeted - $totalSpent : $totalIncome - $totalSpent;
        $percentageUsed = $totalBudgeted > 0 ? round(($totalSpent / $totalBudgeted) * 100, 1) : 0;

        return [
            'month_name' => ucfirst($monthName) . ' ' . $filterYear,
            'total_budgeted' => (float) $totalBudgeted,
            'total_spent' => (float) $totalSpent,
            'total_income' => (float) $totalIncome,
            'remaining' => (float) $remaining,
            'percentage_used' => $percentageUsed,
            'alert_count' => $alertCount,
            'exceeded_count' => $exceededCount,
            'has_budgets' => $totalBudgeted > 0,
            'scope' => $scope,
            'savings' => $totalIncome - $totalSpent,
        ];
    }
}
