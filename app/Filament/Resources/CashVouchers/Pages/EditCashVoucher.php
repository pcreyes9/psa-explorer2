<?php

namespace App\Filament\Resources\CashVouchers\Pages;

use App\Filament\Resources\CashVouchers\CashVoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;


class EditCashVoucher extends EditRecord
{
    protected static string $resource = CashVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('print')
                ->label('Print Voucher')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(
                    fn () => route(
                        'cash-voucher.pdf',
                        $this->record
                    )
                )
                ->openUrlInNewTab(),

                Action::make('printCheck')
                ->label('Print Check')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->url(
                    fn () => route(
                        'cash-voucher.check',
                        $this->record
                    )
                )
                ->openUrlInNewTab(),
        ];
    }
}
