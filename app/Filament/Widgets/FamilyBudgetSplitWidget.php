<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\TransactionSplit;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FamilyBudgetSplitWidget extends Widget
{
    protected string $view = 'filament.widgets.family-budget-split-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public ?int $selectedCategoryId = null;

    public function mount(): void
    {
        // Selecionar a primeira categoria com orçamento por padrão
        $user = Auth::user();
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));

        $firstBudget = Budget::query()
            ->where('family_id', $user->family_id)
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->first();

        $this->selectedCategoryId = $firstBudget?->category_id;
    }

    public function getCategories(): array
    {
        $user = Auth::user();
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));

        return Budget::query()
            ->where('family_id', $user->family_id)
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->with('category')
            ->get()
            ->mapWithKeys(fn($budget) => [$budget->category_id => $budget->category->name])
            ->toArray();
    }

    public function getMemberSplit(): array
    {
        if (!$this->selectedCategoryId) {
            return [];
        }

        $user = Auth::user();
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));

        $monthStart = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $monthEnd = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();

        // Buscar o orçamento
        $budget = Budget::query()
            ->where('family_id', $user->family_id)
            ->where('category_id', $this->selectedCategoryId)
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->first();

        if (!$budget) {
            return [];
        }

        // Buscar membros da família e seus gastos
        $familyMembers = User::where('family_id', $user->family_id)->get();

        $result = [];
        $totalSpent = 0;

        foreach ($familyMembers as $member) {
            $spent = TransactionSplit::query()
                ->where('user_id', $member->id)
                ->whereHas('transaction', function ($query) use ($monthStart, $monthEnd, $budget) {
                    $query->where('category_id', $budget->category_id)
                        ->where('type', 'expense')
                        ->whereBetween('date', [$monthStart, $monthEnd]);
                })
                ->sum('amount');

            if ($spent > 0) {
                $result[] = [
                    'name' => $member->name,
                    'spent' => (float) $spent,
                    'color' => $this->getColorForIndex(count($result)),
                ];
                $totalSpent += $spent;
            }
        }

        // Calcular porcentagens
        foreach ($result as &$item) {
            $item['percentage'] = $totalSpent > 0 ? round(($item['spent'] / $totalSpent) * 100, 1) : 0;
        }

        // Ordenar por valor gasto
        usort($result, fn($a, $b) => $b['spent'] <=> $a['spent']);

        return [
            'members' => $result,
            'total_spent' => $totalSpent,
            'budget_amount' => (float) $budget->amount,
            'category_name' => $budget->category->name,
        ];
    }

    private function getColorForIndex(int $index): string
    {
        $colors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
        return $colors[$index % count($colors)];
    }

    public function selectCategory(int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function shouldShow(): bool
    {
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');
        return $scope === 'family' && count($this->getCategories()) > 0;
    }
}
