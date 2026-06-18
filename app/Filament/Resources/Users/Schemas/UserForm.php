<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                            ->relationship(
                                'roles',
                                'name',
                                fn (Builder $query) => auth()->user()?->hasRole('Super Admin')
                                    ? $query
                                    : $query->where('name', '!=', 'Super Admin')
                            )
                            ->multiple()
                            ->preload()
                            ->visible(fn () => auth()->user()?->can('manage-roles'))
                            ->disabled(function ($record) {
                                if (! $record) {
                                    return false;
                                }

                                // Cannot edit your own roles
                                if (auth()->id() === $record->id) {
                                    return true;
                                }

                                // Only Super Admin can modify a Super Admin's roles
                                if (
                                    $record->hasRole('Super Admin') &&
                                    ! auth()->user()?->hasRole('Super Admin')
                                ) {
                                    return true;
                                }

                                return false;
                            })
                            ->helperText(function ($record) {
                                if (! $record) {
                                    return null;
                                }

                                if (auth()->id() === $record->id) {
                                    return 'You cannot modify your own role assignments.';
                                }

                                if (
                                    $record->hasRole('Super Admin') &&
                                    ! auth()->user()?->hasRole('Super Admin')
                                ) {
                                    return 'Only Super Admin can modify Super Admin role assignments.';
                                }

                                return null;
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