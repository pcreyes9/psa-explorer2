<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('mem_last_name')
            ->columns([
                TextColumn::make('member_id_no')
                    ->label('Member ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mem_last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mem_first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('psa_chapter_code')
                    ->label('Chapter')
                    ->searchable(),

                TextColumn::make('psa_mem_type')
                    ->label('Membership Type')
                    ->badge(),

                TextColumn::make('psa_mem_stat')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('mem_mobile_no1')
                    ->label('Mobile'),

                TextColumn::make('mem_email_address')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('datejoin')
                    ->label('Date Joined')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}