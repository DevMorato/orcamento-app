<?php

namespace App\Filament\Resources\Budgets;

use App\Filament\Resources\Budgets\Pages;
use App\Models\Budget;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $modelLabel = 'Orçamento';

    protected static ?string $pluralModelLabel = 'Orçamentos';

    protected static ?string $navigationLabel = 'Orçamentos';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Categoria')
                    ->options(
                        Category::query()
                            ->where('type', 'expense')
                            ->where(function ($query) use ($user) {
                                $query->where('family_id', $user->family_id)
                                    ->orWhereNull('family_id');
                            })
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),

                TextInput::make('amount')
                    ->label('Valor do Orçamento (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->minValue(0.01),

                Select::make('month')
                    ->label('Mês')
                    ->options([
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
                    ])
                    ->default($currentMonth)
                    ->required(),

                Select::make('year')
                    ->label('Ano')
                    ->options(function () use ($currentYear) {
                        $years = [];
                        for ($y = $currentYear - 1; $y <= $currentYear + 2; $y++) {
                            $years[$y] = $y;
                        }
                        return $years;
                    })
                    ->default($currentYear)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Orçamento')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('spent')
                    ->label('Gasto')
                    ->money('BRL')
                    ->state(fn(Budget $record) => $record->getSpentAmount()),

                TextColumn::make('percentage')
                    ->label('% Usado')
                    ->badge()
                    ->state(fn(Budget $record) => $record->getPercentageUsed() . '%')
                    ->color(fn(Budget $record) => match (true) {
                        $record->getPercentageUsed() >= 100 => 'danger',
                        $record->getPercentageUsed() >= 80 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('remaining')
                    ->label('Restante')
                    ->money('BRL')
                    ->state(fn(Budget $record) => $record->getRemainingAmount())
                    ->color(fn(Budget $record) => $record->isOverBudget() ? 'danger' : 'success'),

                TextColumn::make('period')
                    ->label('Período')
                    ->state(fn(Budget $record) => sprintf('%02d/%d', $record->month, $record->year)),
            ])
            ->defaultSort('month', 'desc')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('family_id', Auth::user()->family_id);
    }
}
