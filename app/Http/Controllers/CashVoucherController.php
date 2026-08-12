<?php

namespace App\Http\Controllers;

use App\Models\CashVoucher;
use Barryvdh\DomPDF\Facade\Pdf;

class CashVoucherController extends Controller
{
    public function voucherPdf(CashVoucher $cashVoucher)
    {
        $cashVoucher->load('items');

        $items = $cashVoucher->items
            ->map(function ($item) {
                return [
                    'description' => $item->description,
                    'amount' => (float) $item->amount,
                ];
            })
            ->toArray();

        $totalAmount = collect($items)->sum('amount');

        $pdf = Pdf::loadView(
            'pdf.voucher',
            [
                'voucherNo' => $cashVoucher->voucher_no,
                'date' => $cashVoucher->date,
                'payTo' => $cashVoucher->pay_to,
                'address' => $cashVoucher->address,
                'checkNo' => $cashVoucher->check_no,

                'approvedBy' => $cashVoucher->approved_by,
                'checkedBy' => $cashVoucher->checked_by,
                'receivedBy' => $cashVoucher->received_by,

                'items' => $items,
                'totalAmount' => $totalAmount,
            ]
        );

        $pdf->setPaper('letter', 'portrait');

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'inline; filename="Cash Voucher.pdf"',
            ]
        );
    }


    public function checkPdf(CashVoucher $cashVoucher)
    {
        $cashVoucher->load('items');

        $items = $cashVoucher->items
            ->map(function ($item) {
                return [
                    'description' => $item->description,
                    'amount' => (float) $item->amount,
                ];
            })
            ->toArray();

        $totalAmount = collect($items)->sum('amount');

        $amountInWords = $this->amountToWords($totalAmount);

        $pdf = Pdf::loadView(
            'pdf.check',
            [
                'date'          => $cashVoucher->date,
                'payTo'         => $cashVoucher->pay_to,
                'amount'        => $totalAmount,
                'amountInWords' => $amountInWords,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Change this if your check uses a custom paper size
        |--------------------------------------------------------------------------
        */

        $pdf->setPaper('letter', 'landscape');

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'inline; filename="Check Output.pdf"',
            ]
        );
    }

    private function amountToWords(float $amount): string
    {
        $whole = (int) floor($amount);

        $decimal = (int) round(
            ($amount - $whole) * 100
        );

        // Handle rounding that produces 100 centavos
        if ($decimal === 100) {
            $whole++;
            $decimal = 0;
        }

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

        $convert = function ($number) use (
            &$convert,
            $ones,
            $tens
        ): string {

            if ($number < 20) {
                return $ones[$number];
            }

            if ($number < 100) {
                return $tens[intdiv($number, 10)]
                    . ($number % 10
                        ? ' ' . $ones[$number % 10]
                        : '');
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

            $centavoWords = $convert($decimal);

            return $words
                . ' PESOS AND '
                . $centavoWords
                . ' CENTAVOS ONLY';
        }

        return $words . ' PESOS ONLY';
    }
}