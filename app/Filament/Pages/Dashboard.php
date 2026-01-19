<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\ActionSize;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    // Disable default widgets to use our grid
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\ExpensesByMemberChart::class,
            \App\Filament\Widgets\IncomeVsExpenseChart::class,
            \App\Filament\Widgets\ExpensesByCategoryChart::class,
            \App\Filament\Widgets\RecentTransactions::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        $scope = request()->query('scope', 'user');
        $isFamily = $scope === 'family';

        return [
            Action::make('toggle_scope')
                ->label($isFamily ? 'Ver Minhas Finanças' : 'Ver Finanças da Família')
                ->icon($isFamily ? 'heroicon-o-user' : 'heroicon-o-users')
                ->color('gray')
                ->url(fn() => route('filament.admin.pages.dashboard', ['scope' => $isFamily ? 'family' : 'user'])),

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
