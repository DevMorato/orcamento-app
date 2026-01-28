<?php

namespace App\Filament\Resources\RecurringTransactions;

use App\Filament\Resources\RecurringTransactions\Pages;
use App\Models\RecurringTransaction;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Account;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class RecurringTransactionResource extends Resource
{
    protected static ?string $model = RecurringTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $modelLabel = 'Transação Recorrente';

    protected static ?string $pluralModelLabel = 'Transações Recorrentes';

    protected static ?string $navigationLabel = 'Recorrências';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'income' => 'Receita',
                        'expense' => 'Despesa',
                    ])
                    ->required()
                    ->default('expense')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('category_id', null);
                        $set('subcategory_id', null);
                    }),

                TextInput::make('description')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Salário, Aluguel, Netflix...'),

                TextInput::make('amount')
                    ->label('Valor (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->minValue(0.01),

                Select::make('frequency')
                    ->label('Frequência')
                    ->options([
                        'daily' => 'Diária',
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensal',
                        'yearly' => 'Anual',
                    ])
                    ->required()
                    ->default('monthly')
                    ->live(),

                Select::make('day_of_month')
                    ->label('Dia do Mês')
                    ->options(array_combine(range(1, 31), range(1, 31)))
                    ->visible(fn(Get $get) => $get('frequency') === 'monthly')
                    ->default(1),

                Select::make('day_of_week')
                    ->label('Dia da Semana')
                    ->options([
                        0 => 'Domingo',
                        1 => 'Segunda',
                        2 => 'Terça',
                        3 => 'Quarta',
                        4 => 'Quinta',
                        5 => 'Sexta',
                        6 => 'Sábado',
                    ])
                    ->visible(fn(Get $get) => $get('frequency') === 'weekly')
                    ->default(1),

                Select::make('category_id')
                    ->label('Categoria')
                    ->options(function (Get $get) use ($user) {
                        $type = $get('type');
                        if (!$type)
                            return [];

                        return Category::query()
                            ->where('type', $type)
                            ->where(function ($query) use ($user) {
                                $query->where('family_id', $user->family_id)
                                    ->orWhereNull('family_id');
                            })
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('subcategory_id', null)),

                Select::make('subcategory_id')
                    ->label('Subcategoria')
                    ->options(function (Get $get) {
                        $categoryId = $get('category_id');
                        if (!$categoryId)
                            return [];

                        return Subcategory::query()
                            ->where('category_id', $categoryId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->placeholder('Selecione (Opcional)'),

                Select::make('account_id')
                    ->label('Conta')
                    ->options(function () use ($user) {
                        return Account::query()
                            ->where('family_id', $user->family_id)
                            ->where('is_active', true)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->placeholder('Selecione (Opcional)'),

                TextInput::make('establishment')
                    ->label('Estabelecimento')
                    ->visible(fn(Get $get) => $get('type') === 'expense')
                    ->maxLength(255),

                DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->default(now()),

                DatePicker::make('end_date')
                    ->label('Data de Término')
                    ->placeholder('Sem término definido')
                    ->after('start_date'),

                DatePicker::make('next_due_date')
                    ->label('Próximo Vencimento')
                    ->required()
                    ->default(now()),

                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'income' ? 'Receita' : 'Despesa')
                    ->color(fn(string $state) => $state === 'income' ? 'success' : 'danger'),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('frequency')
                    ->label('Frequência')
                    ->formatStateUsing(fn(RecurringTransaction $record) => $record->getFrequencyLabel()),

                TextColumn::make('category.name')
                    ->label('Categoria'),

                TextColumn::make('next_due_date')
                    ->label('Próximo Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn(RecurringTransaction $record) => $record->isDue() ? 'warning' : 'gray'),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->defaultSort('next_due_date')
            ->filters([
                //
            ])
            ->actions([
                Action::make('process')
                    ->label('Processar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn(RecurringTransaction $record) => $record->isDue())
                    ->action(function (RecurringTransaction $record) {
                        $record->createTransaction();
                    }),
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
            'index' => Pages\ListRecurringTransactions::route('/'),
            'create' => Pages\CreateRecurringTransaction::route('/create'),
            'edit' => Pages\EditRecurringTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('family_id', Auth::user()->family_id);
    }
}
