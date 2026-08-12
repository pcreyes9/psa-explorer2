<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CashVoucher;
use Illuminate\Support\Facades\DB;

use App\Exports\DisbursementJournalExport;
use Maatwebsite\Excel\Facades\Excel;

class CheckVoucher extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-printer';

    protected static ?string $title = 'Check and Voucher Printing';

    public static function getNavigationLabel(): string { return 'Check and Voucher'; }

    public static function getNavigationGroup(): ?string { return 'Printing'; }
    protected string $view = 'filament.pages.check-voucher';
    public $voucherNo, $date = "08/12/2026", $payTo = "MARSHA MORENO", $address = "123 Main Street", $checkNo = "6000099799", $approvedBy = "FB MAYUGA", $checkedBy = "CS LUNAS", $receivedBy = "MS MORENO", $exportDate, $items = [];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->exportDate = now()->format('Y-m-d');
    }

    public function resetForm()
    {
        $this->reset();

        $this->date = now()->format('Y-m-d');
    }

    public function printVoucher()
    {
        // Add your voucher PDF generation here.
        //  dd($this->items);
         $items = collect($this->items)
            ->map(function ($item) {
                return [
                    'description' => mb_convert_encoding(
                        $item['description'] ?? '',
                        'UTF-8',
                        'UTF-8'
                    ),
                    'amount' => (float) ($item['amount'] ?? 0),
                ];
            })
            ->toArray();

        $pdf = Pdf::loadView(
            'pdf.voucher',
            [
                'voucherNo'  => mb_convert_encoding($this->voucherNo ?? '', 'UTF-8', 'UTF-8'),
                'date'       => $this->date,
                'payTo'      => mb_convert_encoding($this->payTo ?? '', 'UTF-8', 'UTF-8'),
                'address'    => mb_convert_encoding($this->address ?? '', 'UTF-8', 'UTF-8'),
                'checkNo'    => mb_convert_encoding($this->checkNo ?? '', 'UTF-8', 'UTF-8'),
                'approvedBy' => mb_convert_encoding($this->approvedBy ?? '', 'UTF-8', 'UTF-8'),
                'checkedBy'  => mb_convert_encoding($this->checkedBy ?? '', 'UTF-8', 'UTF-8'),
                'receivedBy' => mb_convert_encoding($this->receivedBy ?? '', 'UTF-8', 'UTF-8'),
                'items'      => $items,
            ]
        );

        $this->saveVoucher();
        $this->printCheck();

        $pdf->setPaper('letter', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Cash Voucher.pdf'
        );
    }

    public function printCheck()
    {
        $amount = collect($this->items)->sum(function ($item) {
            return (float) $item['amount'];
        });

        $amountInWords = $this->amountToWords($amount);

        $pdf = Pdf::loadView(
            'pdf.check',
            [
                'date'          => $this->date,
                'payTo'         => $this->payTo,
                'amount'        => $amount,
                'amountInWords' => $amountInWords,
            ]
        );

        $pdf->setPaper('letter', 'landscape');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'Check Output.pdf'
        );
    }

    private function amountToWords(float $amount): string
    {
        $whole = (int) floor($amount);
        $decimal = (int) round(($amount - $whole) * 100);

        $ones = [
            '',
            'ONE',
            'TWO',
            'THREE',
            'FOUR',
            'FIVE',
            'SIX',
            'SEVEN',
            'EIGHT',
            'NINE',
            'TEN',
            'ELEVEN',
            'TWELVE',
            'THIRTEEN',
            'FOURTEEN',
            'FIFTEEN',
            'SIXTEEN',
            'SEVENTEEN',
            'EIGHTEEN',
            'NINETEEN',
        ];

        $tens = [
            '',
            '',
            'TWENTY',
            'THIRTY',
            'FORTY',
            'FIFTY',
            'SIXTY',
            'SEVENTY',
            'EIGHTY',
            'NINETY',
        ];

        $convert = function ($number) use (&$convert, $ones, $tens): string {

            if ($number < 20) {
                return $ones[$number];
            }

            if ($number < 100) {
                return $tens[intdiv($number, 10)]
                    . ($number % 10 ? ' ' . $ones[$number % 10] : '');
            }

            if ($number < 1000) {
                return $ones[intdiv($number, 100)]
                    . ' HUNDRED'
                    . ($number % 100
                        ? ' ' . $convert($number % 100)
                        : '');
            }

            if ($number < 1000000) {
                return $convert(intdiv($number, 1000))
                    . ' THOUSAND'
                    . ($number % 1000
                        ? ' ' . $convert($number % 1000)
                        : '');
            }

            if ($number < 1000000000) {
                return $convert(intdiv($number, 1000000))
                    . ' MILLION'
                    . ($number % 1000000
                        ? ' ' . $convert($number % 1000000)
                        : '');
            }

            return $convert(intdiv($number, 1000000000))
                . ' BILLION'
                . ($number % 1000000000
                    ? ' ' . $convert($number % 1000000000)
                    : '');
        };

        $words = $convert($whole);

        if ($words === '') {
            $words = 'ZERO';
        }

        if ($decimal > 0) {
            return $words
                . ' PESOS AND '
                . str_pad($decimal, 2, '0', STR_PAD_LEFT)
                . '/100 ONLY';
        }

        return $words . ' PESOS ONLY';
    }
    public function saveVoucher()
    {
        $this->validate([
            'voucherNo' => 'required|string|max:100',
            'date' => 'required|date',
            'payTo' => 'required|string|max:255',
            'address' => 'nullable|string',
            'checkNo' => 'nullable|string|max:100',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {

            $total = collect($this->items)->sum(function ($item) {
                return (float) $item['amount'];
            });

            $voucher = CashVoucher::create([
                'voucher_no' => $this->voucherNo,
                'date' => $this->date,
                'pay_to' => $this->payTo,
                'address' => $this->address,
                'check_no' => $this->checkNo,

                'total_amount' => $total,

                'approved_by' => $this->approvedBy,
                'checked_by' => $this->checkedBy,
                'received_by' => $this->receivedBy,
            ]);

            foreach ($this->items as $item) {

                $voucher->items()->create([
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);

            }

        });

        \Filament\Notifications\Notification::make()
            ->title('Cash Voucher Saved')
            ->success()
            ->send();
    }
    public function exportDisbursementVoucher()
    {
        $this->validate([
            'exportDate' => 'required|date',
        ]);

        return Excel::download(
            new DisbursementJournalExport(
                $this->exportDate
            ),
            'Disbursement Journal ' .
            \Carbon\Carbon::parse($this->exportDate)->format('F Y') .
            '.xlsx'
        );
    }
}
