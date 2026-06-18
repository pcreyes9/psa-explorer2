<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->visible(fn () => auth()->user()?->can('manage-roles'))
                            ->disabled(function ($record) {
                                if (! $record) {
                                    return false;
                                }

                                return auth()->id() === $record->id;
                            })
                            ->helperText(function ($record) {
                                if (! $record) {
                                    return null;
                                }

                                return auth()->id() === $record->id
                                    ? 'You cannot modify your own role assignments.'
                                    : null;
                            }),

                        Toggle::make('is_active')
                            ->default(true)
                            ->disabled(function ($record) {
                                if (! $record) {
                                    return false;
                                }

                                if (! $record->hasRole('Super Admin')) {
                                    return false;
                                }

                                $superAdminCount = User::role('Super Admin')->count();

                                return $superAdminCount <= 1;
                            })
                            ->helperText(function ($record) {
                                if (! $record) {
                                    return null;
                                }

                                if (
                                    $record->hasRole('Super Admin') &&
                                    User::role('Super Admin')->count() <= 1
                                ) {
                                    return 'The last Super Admin cannot be deactivated.';
                                }

                                return null;
                            }),
                    ])
                    ->columns(2),
            ]);
    }
}