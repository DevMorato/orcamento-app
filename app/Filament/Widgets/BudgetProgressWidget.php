<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BudgetProgressWidget extends Widget
{
    protected string $view = 'filament.widgets.budget-progress-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public function getBudgets(): array
    {
        $user = Auth::user();

        // Obter mês/ano do filtro
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');

        $budgets = Budget::query()
            ->where('family_id', $user->family_id)
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->with('category')
            ->get();

        $result = [];

        foreach ($budgets as $budget) {
            // Calcular gastos baseado no escopo
            if ($scope === 'family') {
                $spent = $budget->getSpentAmount();
            } else {
                // Para escopo "user", calcular apenas gastos do usuário
                $spent = $this->getUserSpentAmount($budget, $user->id);
            }

            $percentage = $budget->amount > 0
                ? round(($spent / $budget->amount) * 100, 1)
                : 0;

            $result[] = [
                'category' => $budget->category->name,
                'icon' => $budget->category->icon ?? '📊',
                'budget' => (float) $budget->amount,
                'spent' => $spent,
                'remaining' => max(0, $budget->amount - $spent),
                'percentage' => min($percentage, 150), // Cap visual em 150%
                'percentage_real' => $percentage,
                'status' => $this->getStatus($percentage),
                'color' => $this->getColor($percentage),
            ];
        }

        // Ordenar por porcentagem (maiores primeiro)
        usort($result, fn($a, $b) => $b['percentage_real'] <=> $a['percentage_real']);

        return $result;
    }

    private function getUserSpentAmount(Budget $budget, int $userId): float
    {
        // Busca gastos do usuário através dos splits
        return \App\Models\TransactionSplit::query()
            ->where('user_id', $userId)
            ->whereHas('transaction', function ($query) use ($budget) {
                $query->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereMonth('date', $budget->month)
                    ->whereYear('date', $budget->year);
            })
            ->sum('amount');
    }

    private function getStatus(float $percentage): string
    {
        if ($percentage >= 100)
            return 'danger';
        if ($percentage >= 80)
            return 'warning';
        if ($percentage >= 50)
            return 'info';
        return 'success';
    }

    private function getColor(float $percentage): string
    {
        if ($percentage >= 100)
            return '#ef4444'; // red-500
        if ($percentage >= 80)
            return '#f59e0b'; // amber-500
        if ($percentage >= 50)
            return '#3b82f6'; // blue-500
        return '#22c55e'; // green-500
    }

    public function getTotalBudget(): float
    {
        return array_sum(array_column($this->getBudgets(), 'budget'));
    }

    public function getTotalSpent(): float
    {
        return array_sum(array_column($this->getBudgets(), 'spent'));
    }

    public function getOverallPercentage(): float
    {
        $total = $this->getTotalBudget();
        return $total > 0 ? round(($this->getTotalSpent() / $total) * 100, 1) : 0;
    }
}
