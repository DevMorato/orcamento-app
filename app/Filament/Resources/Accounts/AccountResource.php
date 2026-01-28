<?php

namespace App\Filament\Resources\Accounts;

use App\Filament\Resources\Accounts\Pages;
use App\Models\Account;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'Conta';

    protected static ?string $pluralModelLabel = 'Contas';

    protected static ?string $navigationLabel = 'Contas Bancárias';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome da Conta')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Nubank, Itaú, Caixa...'),

                Select::make('type')
                    ->label('Tipo de Conta')
                    ->options([
                        'checking' => 'Conta Corrente',
                        'savings' => 'Poupança',
                        'credit_card' => 'Cartão de Crédito',
                        'cash' => 'Dinheiro',
                        'investment' => 'Investimento',
                    ])
                    ->required()
                    ->default('checking'),

                TextInput::make('initial_balance')
                    ->label('Saldo Inicial (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->default(0)
                    ->helperText('Informe o saldo atual da conta para começar a usar'),

                ColorPicker::make('color')
                    ->label('Cor')
                    ->default('#3B82F6'),

                Toggle::make('is_active')
                    ->label('Conta Ativa')
                    ->default(true)
                    ->helperText('Contas inativas não aparecem nas opções de transação'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'checking' => 'Conta Corrente',
                        'savings' => 'Poupança',
                        'credit_card' => 'Cartão de Crédito',
                        'cash' => 'Dinheiro',
                        'investment' => 'Investimento',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'checking' => 'primary',
                        'savings' => 'success',
                        'credit_card' => 'warning',
                        'cash' => 'gray',
                        'investment' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('balance')
                    ->label('Saldo Atual')
                    ->money('BRL')
                    ->state(fn(Account $record) => $record->getCurrentBalance())
                    ->color(fn(Account $record) => $record->getCurrentBalance() >= 0 ? 'success' : 'danger'),

                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->defaultSort('name')
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('family_id', Auth::user()->family_id);
    }
}
