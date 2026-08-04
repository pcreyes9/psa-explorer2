<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Payment;


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

                TextColumn::make('or_no')
                    ->label('OR No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('member.member_id_no')
                    ->label('PSA ID No.')
                    ->sortable(),

                TextColumn::make('payment_name')
                    ->label('Member Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_total_amt')
                    ->label('Amount')
                    ->money('PHP')
                    ->alignEnd()
                    ->sortable(),

                // TextColumn::make('payment_type')
                //     ->label('Payment Type')
                //     ->badge()
                //     ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('userid')
                    ->label('Processed By'),

                // TextColumn::make('stat')
                //     ->label('Status')
                //     ->badge()
                //     ->formatStateUsing(fn ($state) => $state ? 'Posted' : 'Cancelled'),

            ])

            ->recordAction('view')

            ->recordActions([

                Action::make('view')
                    // ->hidden()
                    ->modalHeading('Payment Details')
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (Payment $record) => view(
                        'filament.payments.payment-details', [
                            'payment' => $record,
                        ]
                    )),

            ]);
    }
}