<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('family_id')
                    ->relationship('family', 'name'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
            ]);
    }
}
