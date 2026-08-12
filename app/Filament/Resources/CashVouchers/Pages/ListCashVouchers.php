<?php

namespace App\Filament\Resources\CashVouchers\Pages;

use App\Exports\DisbursementJournalExport;
use App\Filament\Resources\CashVouchers\CashVoucherResource;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

use Maatwebsite\Excel\Facades\Excel;

class ListCashVouchers extends ListRecords
{
    protected static string $resource = CashVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | CREATE VOUCHER
            |--------------------------------------------------------------------------
            */

            CreateAction::make()
                ->label('Create Cash Voucher')
                ->icon('heroicon-o-plus'),


            /*
            |--------------------------------------------------------------------------
            | EXPORT DISBURSEMENT
            |--------------------------------------------------------------------------
            */

            Action::make('exportDisbursement')
                ->label('Export Disbursement')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')

                ->schema([

                    DatePicker::make('date')
                        ->label('Disbursement Date')
                        ->required()
                        ->default(now()),

                ])

                ->action(function (array $data) {

                    return Excel::download(
                        new DisbursementJournalExport(
                            $data['date']
                        ),
                        'Disbursement Journal ' .
                        \Carbon\Carbon::parse($data['date'])->format('mdY') .
                        '.xlsx'
                    );

                }),

        ];
    }
}