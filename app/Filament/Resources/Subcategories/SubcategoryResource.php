<?php

namespace App\Filament\Resources\Subcategories;

use App\Filament\Resources\Subcategories\Pages;
use App\Models\Subcategory;
use App\Models\Category;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubcategoryResource extends Resource
{
    protected static ?string $model = Subcategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Subcategorias';

    protected static ?string $modelLabel = 'Subcategoria';

    protected static ?string $pluralModelLabel = 'Subcategorias';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Form::make()
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->options(function () {
                                return Category::where(function ($query) {
                                    $query->where('family_id', auth()->user()->family_id)
                                        ->orWhereNull('family_id');
                                })->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Subcategoria Padrão do Sistema')
                            ->disabled()
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->url(fn(Subcategory $record): string => Pages\EditSubcategory::getUrl(['record' => $record])),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('category', function ($query) {
                $query->where('family_id', auth()->user()->family_id)
                    ->orWhereNull('family_id');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubcategories::route('/'),
            'create' => Pages\CreateSubcategory::route('/create'),
            'edit' => Pages\EditSubcategory::route('/{record}/edit'),
        ];
    }
}