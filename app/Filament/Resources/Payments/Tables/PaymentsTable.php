<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('payment_date', 'desc')
            ->columns([
                TextColumn::make('payment_ref_no')
                    ->label('Reference No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('member.member_id_no')
                    ->label('Member ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_name')
                    ->label('Member Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_total_amt')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('payment_type')
                    ->label('Payment Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('userid')
                    ->label('Processed By')
                    ->sortable(),

                TextColumn::make('stat')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Posted' : 'Cancelled'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}