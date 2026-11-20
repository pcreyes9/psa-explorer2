<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class OfficialReceipt extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Official Receipt';

    protected static ?string $title = 'Official Receipt (Services)';

    protected static string|\UnitEnum|null $navigationGroup = 'Printing';

    protected string $view = 'filament.pages.official-receipt';

    public array $receipt = [];

    public array $memberCategories = [];

    public array $registrationFees = [];

    public string $paymentMethod = '';

    public string $paymentFor = '';


    public function mount(): void
    {
        $this->receipt = [
            'payment_date' => now()->format('Y-m-d'),
            'received_from' => '',
            'tin' => '',
            'business_name' => '',
            'address' => '',
            'amount_words' => '',
            'amount' => '',
            'fiscal_year' => '',
            'regular_members_amount' => '',
            'trainee_members_amount' => '',
            'trainee_year_level' => '',
            'other_registration' => '',
            'other_assessment' => '',
            'other_assessment_amount' => '',
            'bank' => '',
            'payment_number' => '',
            'payment_date_detail' => '',
            'pmo_number' => '',
            'received_by' => '',
        ];
    }

    public function updatedReceipt($value, $key): void
    {
        if ($key === 'amount') {
            $this->receipt['amount_words'] = is_numeric($value)
                ? $this->amountToWords((float) $value)
                : '';
        }
    }

    public function resetForm(): void
    {
        $this->mount();
        $this->memberCategories = [];
        $this->registrationFees = [];
        $this->paymentMethod = '';
        $this->paymentFor = '';
    }

    public function printReceipt(): void
    {
        $this->dispatch('print-receipt');
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
                . $convert($decimal)
                . ($decimal === 1 ? ' CENTAVO ONLY' : ' CENTAVOS ONLY');
        }

        return $words . ' PESOS ONLY';
    }
}
