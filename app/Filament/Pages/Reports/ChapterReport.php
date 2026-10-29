<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Illuminate\Support\Facades\DB;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ChapterReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.reports.chapter-report';

    protected static ?string $title = 'Chapter Report';

    protected static ?string $navigationLabel = 'Chapter Report';

    protected static ?string $slug = 'chapter-report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public string $asOfDate;

    public array $summary = [
        'good_standing' => 0,
        'delinquent' => 0,
        'total_members' => 0,
    ];

    public array $chapterReport = [];

    public function mount(): void
    {
        $this->asOfDate = now()->toDateString();
    }

    /**
     * Refresh the report when the as-of date changes.
     */
    public function updatedAsOfDate(): void
    {
        $this->resetPageData();
    }

    /**
     * Reset report data until the validated calculation is connected.
     */
    protected function resetPageData(): void
    {
        $this->summary = [
            'good_standing' => 0,
            'delinquent' => 0,
            'total_members' => 0,
        ];

        $this->chapterReport = [];
    }

    public function generateReport(): void
    {
        
        $this->validate([
            'asOfDate' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $this->resetPageData();
    }
}
