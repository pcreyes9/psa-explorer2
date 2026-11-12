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
                number_format(Member::activeMembers()->count())
            )
                ->description('Active Members')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total Regular Members',
                number_format(Member::activeMembers()->where('psa_mem_type', 'RM')->count())
            )
                ->description('Total Regular Members')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total Trainee Members',
                number_format(Member::activeMembers()->where('psa_mem_type', 'TM')->count())
            )
                ->description('Total Trainee Members')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total Life Members',
                number_format(Member::activeMembers()->where('psa_mem_type', 'LM')->count())
            )
                ->description('Total Life Members')
                ->icon('heroicon-o-users'),

        ];
    }
}
