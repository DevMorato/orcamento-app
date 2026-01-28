<?php

namespace App\Filament\Widgets;

use App\Models\TransactionSplit;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ExpensesByMemberChart extends ChartWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    public ?string $scope = 'user';

    public function getHeading(): ?string
    {
        return 'Despesas por Membro (Mês)';
    }

    public function mount(): void
    {
        $this->scope = request()->query('scope', 'user');
        parent::mount();
    }

    protected function getData(): array
    {
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');
        $user = Auth::user();

        // Obter mês/ano do filtro
        $filterMonth = (int) (request()->query('month') ?: session('dashboard_month', now()->month));
        $filterYear = (int) (request()->query('year') ?: session('dashboard_year', now()->year));

        $monthStart = \Carbon\Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $monthEnd = \Carbon\Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();

        $query = TransactionSplit::query()
            ->join('transactions', 'transaction_splits.transaction_id', '=', 'transactions.id')
            ->join('users', 'transaction_splits.user_id', '=', 'users.id')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$monthStart, $monthEnd]);

        if ($scope === 'family') {
            // Show all family members
            $query->where('transactions.family_id', $user->family_id);
        } else {
            // Show only current user
            $query->where('transaction_splits.user_id', $user->id);
        }

        // Group by user and sum amounts
        $data = $query
            ->selectRaw('users.id as user_id, users.name as user_name, sum(transaction_splits.amount) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Despesas',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => '#f87171',
                ],
            ],
            'labels' => $data->pluck('user_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
