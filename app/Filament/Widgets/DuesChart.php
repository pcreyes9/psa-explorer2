<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentItem;

class AnnualDuesChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Membership Dues Collection';

    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = PaymentItem::query()
            ->join('payments as p', 'payment_items.payment_ref_no', '=', 'p.payment_ref_no')
            ->selectRaw("
                MONTH(p.payment_date) as month,
                SUM(payment_items.amount_due) as total
            ")
            ->whereYear('p.payment_date', now()->year)
            ->whereMonth('p.payment_date', '<', now()->month)
            ->where('payment_items.tran_code', 'MEMF')
            ->groupBy(DB::raw('MONTH(p.payment_date)'))
            ->orderBy(DB::raw('MONTH(p.payment_date)'))
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Membership Dues',
                    'data' => $data->pluck('total')->toArray(),
                ],
            ],
            'labels' => $data->pluck('month')
                ->map(fn ($month) => date('M', mktime(0, 0, 0, $month, 1)))
                ->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [

            'responsive' => true,

            'fill' => true,

            'maintainAspectRatio' => false,

            'plugins' => [

                'legend' => [
                    'position' => 'bottom',

                    'labels' => [
                        // 'usePointStyle' => true,
                        'pointStyle' => 'square',
                        'padding' => 20,
                        'font' => [
                            'size' => 13,
                            'weight' => 'bold',
                        ],
                    ],
                ],

                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],

            ],

            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],

            'scales' => [

                'x' => [
                    'grid' => [
                        'display' => true,
                    ],

                    'ticks' => [
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],

                'y' => [
                    'beginAtZero' => true,

                    'grid' => [
                        'color' => 'rgba(0,0,0,.08)',
                    ],

                    'ticks' => [
                        'font' => [
                            'size' => 12,
                        ],

                        'callback' => 'function(value){ return "₱" + value.toLocaleString(); }',
                    ],
                ],

            ],

        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}