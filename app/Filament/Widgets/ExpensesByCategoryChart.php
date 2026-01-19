<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpensesByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 4;
    public function getHeading(): ?string
    {
        return 'Despesas por Categoria (Mês)';
    }

    #[\Livewire\Attributes\Url]
    public ?string $scope = 'user';

    public function mount(): void
    {
        $this->scope = request()->query('scope', 'user');
        parent::mount();
    }

    protected function getData(): array
    {
        $scope = $this->scope;
        $user = Auth::user();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $query = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$monthStart, $monthEnd]);

        if ($scope === 'family') {
            $query->where('transactions.family_id', $user->family_id);
            $query->selectRaw('categories.name as category, sum(transactions.amount) as total');
        } else {
            // For user scope, we ideally should look at splits, but visually for valid charts, 
            // it is simpler to show Transactions user registered OR splits. 
            // Getting splits by category is complicated because splits don't have category directly.
            // Simplification: Show user's REGISTERED transactions for now, or use splits joining back.

            // Correct approach: Join splits
            // But splits don't have category. Transaction has.
            // Query from Splits -> join Transaction -> join Category

            $query = \App\Models\TransactionSplit::query()
                ->join('transactions', 'transaction_splits.transaction_id', '=', 'transactions.id')
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transaction_splits.user_id', $user->id)
                ->whereBetween('transactions.date', [$monthStart, $monthEnd])
                ->selectRaw('categories.name as category, sum(transaction_splits.amount) as total');
        }

        $data = $query->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Despesas',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        '#f87171',
                        '#fb923c',
                        '#facc15',
                        '#4ade80',
                        '#60a5fa',
                        '#a78bfa',
                        '#e879f9'
                    ],
                ],
            ],
            'labels' => $data->pluck('category'),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
