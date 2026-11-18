<?php

namespace App\Filament\Pages\Reports;

use App\Models\Payment;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PaymentCollectionReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected string $view = 'filament.pages.reports.payment-collection-report';

    protected static ?string $title = 'Payment Collection Report';

    protected static ?string $navigationLabel = 'Payment Collections';

    protected static ?string $slug = 'payment-collection-report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 2;

    public string $fromDate;

    public string $toDate;

    public array $summary = [
        'payment_count' => 0,
        'total_amount' => 0,
        'average_amount' => 0,
    ];

    public array $breakdown = [];

    public function mount(): void
    {
        $this->fromDate = now()->startOfYear()->toDateString();
        $this->toDate = now()->toDateString();

        $this->generateReport();
    }

    public function generateReport(): void
    {
        $this->validate([
            'fromDate' => ['required', 'date', 'before_or_equal:toDate'],
            'toDate' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $from = Carbon::parse($this->fromDate)->startOfDay();
        $to = Carbon::parse($this->toDate)->addDay()->startOfDay();

        $query = Payment::query()
            ->where('payment_date', '>=', $from)
            ->where('payment_date', '<', $to);

        $summary = (clone $query)
            ->selectRaw(
                'COUNT(*) AS payment_count,
                COALESCE(SUM(payment_total_amt), 0) AS total_amount,
                COALESCE(AVG(payment_total_amt), 0) AS average_amount'
            )
            ->toBase()
            ->first();

        $this->summary = [
            'payment_count' => (int) $summary->payment_count,
            'total_amount' => (float) $summary->total_amount,
            'average_amount' => (float) $summary->average_amount,
        ];

        $this->breakdown = (clone $query)
            ->selectRaw(
                'COALESCE(payment_type, \'Unspecified\') AS payment_type,
                COUNT(*) AS payment_count,
                COALESCE(SUM(payment_total_amt), 0) AS total_amount'
            )
            ->groupBy('payment_type')
            ->orderByDesc('total_amount')
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'payment_type' => $row->payment_type,
                'payment_count' => (int) $row->payment_count,
                'total_amount' => (float) $row->total_amount,
            ])
            ->all();
    }
}
