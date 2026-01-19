<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'income' => 'Receita',
                        'expense' => 'Despesa',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    }),
                \Filament\Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('subcategory.name')
                    ->label('Subcategoria')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('establishment')
                    ->label('Estabelecimento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->color(fn($record) => $record->type === 'income' ? 'success' : 'danger'),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'income' => 'Receita',
                        'expense' => 'Despesa',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name', function ($query) {
                        return $query->where(function ($q) {
                            $q->whereNull('family_id')
                                ->orWhere('family_id', auth()->user()->family_id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('user_id')
                    ->label('Membro da Família')
                    ->relationship('user', 'name', function ($query) {
                        return $query->where('family_id', auth()->user()->family_id);
                    }),
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')->label('De'),
                        \Filament\Forms\Components\DatePicker::make('date_until')->label('Até'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->url(fn(\App\Models\Transaction $record): string => \App\Filament\Resources\Transactions\TransactionResource::getUrl('edit', ['record' => $record])),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
