<?php

namespace App\Filament\Resources\CMEPrograms\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CMEProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->orderByDesc('cme_year')
                ->orderBy('cme_startdate')
            )
            ->columns([

                TextColumn::make('cme_program_code')
                    ->label('Program Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cme_year')
                    ->label('Year')
                    ->sortable(),

                // TextColumn::make('cme_program_type')
                //     ->label('Program Type')
                //     ->searchable(),

                TextColumn::make('cme_title')
                    ->label('Program Title')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('cme_startdate')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('cme_enddate')
                    ->label('End Date')
                    ->date()
                    ->sortable(),

                // IconColumn::make('stat')
                //     ->label('Active')
                //     ->boolean(),

            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
