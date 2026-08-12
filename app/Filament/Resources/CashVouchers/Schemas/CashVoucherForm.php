<?php

namespace App\Filament\Resources\CashVouchers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CashVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('voucher_no')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('pay_to')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('check_no'),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('approved_by'),
                TextInput::make('checked_by'),
                TextInput::make('received_by'),
            ]);
    }
}
