<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
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

                TextInput::make('amount')
                    ->label('Valor (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->live(onBlur: true),

                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->default(now()),

                Select::make('category_id')
                    ->label('Categoria')
                    ->options(function (Get $get) {
                        $type = $get('type');
                        $user = Auth::user();
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

                TextInput::make('establishment')
                    ->label('Estabelecimento')
                    ->visible(fn(Get $get) => $get('type') === 'expense'),

                Textarea::make('description')
                    ->label('Descrição')
                    ->columnSpanFull(),

                Repeater::make('splits')
                    ->relationship('splits')
                    ->label('Rateio (Divisão de Despesa)')
                    ->visible(fn(Get $get) => $get('type') === 'expense')
                    ->dehydrated(fn(Get $get) => $get('type') === 'expense')
                    ->schema([
                        Select::make('user_id')
                            ->label('Membro da Família')
                            ->options(function () {
                                $user = Auth::user();
                                if (!$user->family)
                                    return [];
                                return $user->family->users()->pluck('name', 'id');
                            })
                            ->required()
                            ->default(Auth::id())
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                        TextInput::make('percentage')
                            ->label('Porcentagem (%)')
                            ->numeric()
                            ->required()
                            ->default(100)
                            ->suffix('%')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $totalAmount = $get('../../amount');
                                if (is_numeric($totalAmount) && is_numeric($state)) {
                                    $set('amount', round($totalAmount * ($state / 100), 2));
                                }
                            }),

                        TextInput::make('amount')
                            ->label('Valor (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->readOnly()
                            ->required(),
                    ])
                    ->defaultItems(0)
                    ->columnSpanFull()
                    ->live()
                    ->rules([
                        function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('type') !== 'expense') {
                                    return;
                                }

                                if (empty($value)) {
                                    return;
                                }

                                $totalPercentage = collect($value)->sum('percentage');
                                if ($totalPercentage != 100) {
                                    $fail("A soma das porcentagens deve ser 100%. Atual: {$totalPercentage}%");
                                }
                            };
                        },
                    ]),
            ]);
    }
}
