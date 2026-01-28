<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\ActionSize;
use Filament\Forms\Components\Select;
use Filament\Actions\ActionGroup;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    public function mount(): void
    {
        $this->filterMonth = (int) request()->query('month', now()->month);
        $this->filterYear = (int) request()->query('year', now()->year);

        // Salvar na sessão
        session(['dashboard_month' => $this->filterMonth]);
        session(['dashboard_year' => $this->filterYear]);
    }

    public function getWidgets(): array
    {
        // Salvar scope na sessão quando a página é carregada
        $scope = request()->query('scope', session('dashboard_scope', 'user'));
        session(['dashboard_scope' => $scope]);

        return [
            \App\Filament\Widgets\FinancialSummaryWidget::class,
            \App\Filament\Widgets\BudgetAlertsWidget::class,
            \App\Filament\Widgets\BudgetProgressWidget::class,
            \App\Filament\Widgets\FamilyBudgetSplitWidget::class,
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\ExpensesByMemberChart::class,
            \App\Filament\Widgets\IncomeVsExpenseChart::class,
            \App\Filament\Widgets\ExpensesByCategoryChart::class,
            \App\Filament\Widgets\AccountBalancesWidget::class,
            \App\Filament\Widgets\RecentTransactions::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        $scope = request()->query('scope') ?: session('dashboard_scope', 'user');
        $isFamily = $scope === 'family';
        $currentMonth = $this->filterMonth ?? now()->month;
        $currentYear = $this->filterYear ?? now()->year;

        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        return [
            // Filtro de período
            ActionGroup::make([
                Action::make('prev_month')
                    ->label('←')
                    ->color('gray')
                    ->url(function () use ($currentMonth, $currentYear, $scope) {
                        $prevMonth = $currentMonth - 1;
                        $prevYear = $currentYear;
                        if ($prevMonth < 1) {
                            $prevMonth = 12;
                            $prevYear--;
                        }
                        return route('filament.admin.pages.dashboard', [
                            'scope' => $scope,
                            'month' => $prevMonth,
                            'year' => $prevYear,
                        ]);
                    }),
                Action::make('current_period')
                    ->label($months[$currentMonth] . ' ' . $currentYear)
                    ->color('gray')
                    ->url(route('filament.admin.pages.dashboard', [
                        'scope' => $scope,
                        'month' => now()->month,
                        'year' => now()->year,
                    ])),
                Action::make('next_month')
                    ->label('→')
                    ->color('gray')
                    ->url(function () use ($currentMonth, $currentYear, $scope) {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                        if ($nextMonth > 12) {
                            $nextMonth = 1;
                            $nextYear++;
                        }
                        return route('filament.admin.pages.dashboard', [
                            'scope' => $scope,
                            'month' => $nextMonth,
                            'year' => $nextYear,
                        ]);
                    }),
            ])
                ->label($months[$currentMonth] . '/' . $currentYear)
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->button(),

            Action::make('toggle_scope')
                ->label($isFamily ? 'Ver Minhas Finanças' : 'Ver Finanças da Família')
                ->icon($isFamily ? 'heroicon-o-user' : 'heroicon-o-users')
                ->color('gray')
                ->url(fn() => route('filament.admin.pages.dashboard', [
                    'scope' => $isFamily ? 'user' : 'family',
                    'month' => $currentMonth,
                    'year' => $currentYear,
                ])),

            Action::make('new_income')
                ->label('Nova Receita')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(fn() => \App\Filament\Resources\Transactions\TransactionResource::getUrl('create', ['type' => 'income'])),

            Action::make('new_expense')
                ->label('Nova Despesa')
                ->icon('heroicon-o-minus')
                ->color('danger')
                ->url(fn() => \App\Filament\Resources\Transactions\TransactionResource::getUrl('create', ['type' => 'expense'])),
        ];
    }
}
