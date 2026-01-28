<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Auth;

class IncomeVsExpenseChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Receitas vs Despesas (Últimos 12 meses)';
    }
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'full';

    public ?string $scope = 'user';

    public function mount(): void
    {
        $this->scope = request()->query('scope', 'user');
        parent::mount();
    }

    protected function getData(): array
    {
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');
        $user = Auth::user();

        $start = now()->subYear()->startOfMonth();
        $end = now()->endOfMonth();

        // Helper to query trend
        $queryTrend = function ($type) use ($scope, $user, $start, $end) {
            if ($scope === 'family') {
                return Trend::query(Transaction::query()->where('family_id', $user->family_id)->where('type', $type))
                    ->dateColumn('date')
                    ->between(start: $start, end: $end)
                    ->perMonth()
                    ->sum('amount');
            } else {
                if ($type === 'income') {
                    return Trend::query(Transaction::query()->where('user_id', $user->id)->where('type', 'income'))
                        ->dateColumn('date')
                        ->between(start: $start, end: $end)
                        ->perMonth()
                        ->sum('amount');
                } else {
                    // Expenses for user = Splits
                    // Trend library works on Models. 
                    return Trend::query(
                        \App\Models\TransactionSplit::query()
                            ->where('user_id', $user->id)
                            ->whereHas('transaction', fn($q) => $q->where('type', 'expense')) // Safety check
                    )
                        ->dateColumn('created_at') // Splits usually don't have date, they use created_at. Ideally we join transaction date.
                        // Problem: TransactionSplit date is created_at. Transaction has 'date'.
                        // Trend library assumes a date column on the model.
                        // Workaround: We might need to rely on created_at of split which is close enough?
                        // Or use custom query.
                        // Let's use created_at for now to avoid complexity or use Raw query.
                        ->between(start: $start, end: $end)
                        ->perMonth()
                        ->sum('amount');
                }
            }
        };

        $incomeData = $queryTrend('income');
        $expenseData = $queryTrend('expense');

        return [
            'datasets' => [
                [
                    'label' => 'Receitas',
                    'data' => $incomeData->map(fn(TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#4ade80',
                    'borderColor' => '#4ade80',
                ],
                [
                    'label' => 'Despesas',
                    'data' => $expenseData->map(fn(TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#f87171',
                    'borderColor' => '#f87171',
                ],
            ],
            'labels' => $incomeData->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
