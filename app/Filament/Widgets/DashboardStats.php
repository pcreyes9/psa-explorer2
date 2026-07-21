<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make(
                'Total Members',
                number_format(Member::where('mem_stat', 'Active')
                ->where('mem_last_name', '!=', 'Invalid')
                ->where('mem_last_name', '!=', 'TEST')
                ->where('psa_chapter_code', '!=', '-')
                ->where('psa_mem_type', '!=', 'OT')
                ->count())
            )
                ->description('Active Members')
                ->icon('heroicon-o-users'),

            // Stat::make(
            //     'Total Members',
            //     number_format(Member::where('mem_stat', 'Active')->count())
            // )
            //     ->description('Active Members')
            //     ->icon('heroicon-o-users'),

            // Stat::make(
            //     'Active Members',
            //     number_format(
            //         Member::where('mem_stat', 'Active')->where('mem_total_bal', 0)->count()
            //     )
            // )
            //     ->description('Active Members')
            //     ->icon('heroicon-o-user-group'),

            // Stat::make(
            //     'Inactive Members',
            //     number_format(
            //         Member::where('psa_mem_stat', '<>', 'A')->count()
            //     )
            // )
            //     ->description('Inactive Members')
            //     ->icon('heroicon-o-user-minus'),

            // Stat::make(
            //     'Chapters',
            //     number_format(
            //         Member::distinct('psa_chapter_code')->count()
            //     )
            // )
            //     ->description('Active Chapters')
            //     ->icon('heroicon-o-building-office-2'),

            Stat::make(
                'Total Regular Members',
                number_format(Member::where('mem_stat', 'Active')
                ->where('mem_last_name', '!=', 'Invalid')
                ->where('mem_last_name', '!=', 'TEST')
                ->where('psa_chapter_code', '!=', '-')
                ->where('mem_stat', '!=', 'OT')
                ->where('psa_mem_type', '=', 'RM')
                ->count())
            )
                ->description('Total Active Members')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total Trainee Members',
                number_format(Member::where('mem_stat', 'Active')
                ->where('mem_last_name', '!=', 'Invalid')
                ->where('mem_last_name', '!=', 'TEST')
                ->where('psa_chapter_code', '!=', '-')
                ->where('mem_stat', '!=', 'OT')
                ->where('psa_mem_type', '=', 'TM')
                ->count())
            )
                ->description('Total Active Members')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total Life Members',
                number_format(Member::where('mem_stat', 'Active')
                ->where('mem_last_name', '!=', 'Invalid')
                ->where('mem_last_name', '!=', 'TEST')
                ->where('psa_chapter_code', '!=', '-')
                ->where('mem_stat', '!=', 'OT')
                ->where('psa_mem_type', '=', 'LM')
                ->count())
            )
                ->description('Total Active Members')
                ->icon('heroicon-o-users'),
        ];
    }
}
