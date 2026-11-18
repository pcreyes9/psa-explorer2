<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentItem;

class AnnualDuesChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Membership Dues';

    protected ?string $description = 'Membership dues collected this year, excluding the current month';

    protected ?string $maxHeight = '320px';

    protected string $color = 'primary';

    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $today = now();
        $startOfYear = $today->copy()->startOfYear();
        $startOfCurrentMonth = $today->copy()->startOfMonth();

        $data = PaymentItem::query()
            ->join('payments as p', 'payment_items.payment_ref_no', '=', 'p.payment_ref_no')
            ->selectRaw("
                MONTH(p.payment_date) as month,
                SUM(payment_items.amount_due) as total
            ")
            ->where('p.payment_date', '>=', $startOfYear)
            ->where('p.payment_date', '<', $startOfCurrentMonth)
            ->where('payment_items.tran_code', 'MEMF')
            ->groupBy(DB::raw('MONTH(p.payment_date)'))
            ->orderBy(DB::raw('MONTH(p.payment_date)'))
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Membership dues',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(37, 99, 235, 0.72)',
                    'borderColor' => 'rgb(37, 99, 235)',
                    'borderWidth' => 1,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
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
