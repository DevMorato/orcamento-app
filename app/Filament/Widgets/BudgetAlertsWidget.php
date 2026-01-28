<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\TransactionSplit;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BudgetAlertsWidget extends Widget
{
    protected string $view = 'filament.widgets.budget-alerts-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getBudgetAlerts(): array
    {
        $user = Auth::user();

        // Usar filtro de mês/ano
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');

        $budgets = Budget::query()
            ->where('family_id', $user->family_id)
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->with('category')
            ->get();

        $alerts = [];

        foreach ($budgets as $budget) {
            // Calcular gasto baseado no escopo
            if ($scope === 'family') {
                $spent = $budget->getSpentAmount();
                $percentage = $budget->getPercentageUsed();
                $userSpent = null;
                $userPercentage = null;
            } else {
                // Para escopo user, calcular gasto individual
                $spent = $this->getUserSpentAmount($budget, $user->id);
                $percentage = $budget->amount > 0
                    ? round(($spent / $budget->amount) * 100, 1)
                    : 0;
                $userSpent = $spent;
                $userPercentage = $percentage;
            }

            if ($percentage >= 80) {
                $alerts[] = [
                    'category' => $budget->category->name,
                    'budget' => (float) $budget->amount,
                    'spent' => $spent,
                    'remaining' => max(0, $budget->amount - $spent),
                    'percentage' => $percentage,
                    'status' => $percentage >= 100 ? 'danger' : 'warning',
                    'user_spent' => $userSpent,
                    'user_percentage' => $userPercentage,
                    'scope' => $scope,
                ];
            }
        }

        // Ordenar por porcentagem (maiores primeiro)
        usort($alerts, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        return $alerts;
    }

    private function getUserSpentAmount(Budget $budget, int $userId): float
    {
        $monthStart = Carbon::create($budget->year, $budget->month, 1)->startOfMonth();
        $monthEnd = Carbon::create($budget->year, $budget->month, 1)->endOfMonth();

        return TransactionSplit::query()
            ->where('user_id', $userId)
            ->whereHas('transaction', function ($query) use ($budget, $monthStart, $monthEnd) {
                $query->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$monthStart, $monthEnd]);
            })
            ->sum('amount');
    }

    public function hasAlerts(): bool
    {
        return count($this->getBudgetAlerts()) > 0;
    }
}
