<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentTransactions extends BaseWidget
{
    protected static ?int $sort = 9;
    protected int|string|array $columnSpan = 'full';
    public function getHeading(): ?string
    {
        return 'Transações Recentes';
    }

    public ?string $scope = 'user';

    public function mount(): void
    {
        $this->scope = request()->query('scope', 'user');
        if (method_exists(parent::class, 'mount')) {
            parent::mount();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $scope = request()->query('scope') ?: session('dashboard_scope', 'user');
                $user = Auth::user();

                $query = Transaction::query();

                if ($scope === 'family') {
                    $query->where('family_id', $user->family_id);
                } else {
                    // Show transactions where I am the payer OR I have a split
                    $query->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id) // Registered by me
                            ->orWhereHas('splits', fn($s) => $s->where('user_id', $user->id)); // Or I participate
                    });
                }

                return $query->latest('date')->limit(10);
            })
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(30),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->color(fn($record) => $record->type === 'income' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável'),
            ])
            ->actions([
                Action::make('open')
                    ->url(fn(Transaction $record): string => TransactionResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
