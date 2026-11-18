<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentPayments extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Payment Activity')
            ->query(
                Payment::query()
                    ->select([
                        'payment_ref_no',
                        'or_no',
                        'member_id_no',
                        'payment_name',
                        'payment_total_amt',
                        'payment_date',
                        'payment_type',
                    ])
                    ->orderByDesc('payment_date')
                    ->orderByDesc('payment_ref_no')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('payment_ref_no')
                    ->label('Reference')
                    ->color('primary')
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('or_no')
                    ->label('OR No.')
                    ->placeholder('-'),
                TextColumn::make('member_id_no')
                    ->label('Member ID'),
                TextColumn::make('payment_name')
                    ->label('Member Name')
                    ->placeholder('-'),
                TextColumn::make('payment_total_amt')
                    ->label('Amount')
                    ->money('PHP')
                    ->weight('bold')
                    ->alignEnd(),
                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('payment_type')
                    ->label('Method')
                    ->badge()
                    ->placeholder('-'),
            ])
            ->striped()
            ->paginated(false)
            ->poll(null);
    }
}
