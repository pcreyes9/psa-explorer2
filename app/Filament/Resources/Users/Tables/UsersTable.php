<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(function ($record) {
                        return ! (
                            $record->hasRole('Super Admin')
                            && ! auth()->user()?->hasRole('Super Admin')
                        );
                    }),

                DeleteAction::make()
                    ->visible(function ($record) {
                        if (! auth()->user()?->can('users_delete')) {
                            return false;
                        }

                        return ! (
                            $record->hasRole('Super Admin')
                            && ! auth()->user()?->hasRole('Super Admin')
                        );
                    })
                    ->disabled(fn ($record) => $record->hasRole('Super Admin'))
                    ->tooltip(fn ($record) => $record->hasRole('Super Admin')
                        ? 'Super Admin accounts cannot be deleted.'
                        : null),

                ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('users_delete'))
                        ->action(function ($records) {
                            $records
                                ->reject(fn ($record) => $record->hasRole('Super Admin'))
                                ->each
                                ->delete();
                        }),
                ]),
            ]);
    }
}
