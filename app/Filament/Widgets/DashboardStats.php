<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Membership Overview';

    protected ?string $description = 'Current active membership distribution';

    protected function getStats(): array
    {
        $counts = Member::activeMembers()
            ->selectRaw(
                'COUNT(*) AS total_members,
                SUM(CASE WHEN psa_mem_type = ? THEN 1 ELSE 0 END) AS regular_members,
                SUM(CASE WHEN psa_mem_type = ? THEN 1 ELSE 0 END) AS trainee_members,
                SUM(CASE WHEN psa_mem_type = ? THEN 1 ELSE 0 END) AS life_members',
                ['RM', 'TM', 'LM'],
            )
            ->toBase()
            ->first();

        $regularMembers = (int) $counts->regular_members;
        $traineeMembers = (int) $counts->trainee_members;
        $lifeMembers = (int) $counts->life_members;

        return [
            Stat::make('Active Members', number_format((int) $counts->total_members))
                ->description('All active member records')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->icon('heroicon-o-users')
                ->chart([$regularMembers, $traineeMembers, $lifeMembers]),

            Stat::make('Regular Members', number_format($regularMembers))
                ->description('RM membership type')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-user-group'),

            Stat::make('Trainee Members', number_format($traineeMembers))
                ->description('TM membership type')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning')
                ->icon('heroicon-o-user'),

            Stat::make('Life Members', number_format($lifeMembers))
                ->description('LM membership type')
                ->descriptionIcon('heroicon-m-star')
                ->color('info')
                ->icon('heroicon-o-sparkles'),
        ];
    }
}
