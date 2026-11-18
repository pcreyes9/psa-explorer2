<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class ChapterReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.reports.chapter-report';

    protected static ?string $title = 'Chapter Report';

    protected static ?string $navigationLabel = 'Chapter Report';

    protected static ?string $slug = 'chapter-report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public string $asOfDate;

    public array $chapters = [];

    public ?string $selectedChapter = null;

    public array $summary = [
        'total_members' => 0,
        'regular_members' => 0,
        'trainee_members' => 0,
        'life_members' => 0,
        'good_standing' => null,
        'not_good_standing' => null,
    ];

    public function mount(): void
    {
        $this->asOfDate = now()->toDateString();
        $this->loadChapters();
    }

    protected function loadChapters(): void
    {
        $this->chapters = DB::connection('sqlsrv')
            ->table('chapters')
            ->select([
                'psa_chapter_code as code',
                'psa_chapter_desc as name',
            ])
            ->orderBy('psa_chapter_desc')
            ->get()
            ->map(fn ($chapter) => [
                'code' => $chapter->code,
                'name' => $chapter->name ?: $chapter->code,
            ])
            ->all();
    }

    public function selectChapter(string $chapter): void
    {
        if (! collect($this->chapters)->contains('code', $chapter)) {
            return;
        }

        $this->selectedChapter = $chapter;
        $this->loadSummary();
    }

    public function updatedAsOfDate(): void
    {
        $this->resetSummary();
    }

    public function generateReport(): void
    {
        $this->validate([
            'asOfDate' => ['required', 'date', 'before_or_equal:today'],
        ]);

        if ($this->selectedChapter) {
            $this->loadSummary();
        }
    }

    protected function loadSummary(): void
    {
        $summary = DB::connection('sqlsrv')->selectOne(
            <<<'SQL'
                SELECT
                    COUNT(*) AS total_members,
                    SUM(CASE WHEN psa_mem_type = 'RM' THEN 1 ELSE 0 END) AS regular_members,
                    SUM(CASE WHEN psa_mem_type = 'TM' THEN 1 ELSE 0 END) AS trainee_members,
                    SUM(CASE WHEN psa_mem_type = 'LM' THEN 1 ELSE 0 END) AS life_members
                FROM member WITH (NOLOCK)
                WHERE mem_stat = 'Active'
                AND mem_last_name NOT IN ('Invalid', 'TEST')
                AND psa_chapter_code = ?
                AND psa_mem_type <> 'OT'
            SQL,
            [$this->selectedChapter]
        );

        $this->summary = [
            'total_members' => (int) ($summary->total_members ?? 0),
            'regular_members' => (int) ($summary->regular_members ?? 0),
            'trainee_members' => (int) ($summary->trainee_members ?? 0),
            'life_members' => (int) ($summary->life_members ?? 0),
            'good_standing' => null,
            'not_good_standing' => null,
        ];

        $this->loadStandingSummary();
    }

    protected function loadStandingSummary(): void
    {
        $connection = DB::connection('sqlsrv');
        $connection->getPdo()->setAttribute(\PDO::SQLSRV_ATTR_QUERY_TIMEOUT, 3);

        try {
            $standing = $connection->selectOne(
                <<<'SQL'
                    SELECT
                        SUM(CASE
                            WHEN psa_mem_stat IN ('InArrears', 'In Arrears', 'Delinquent')
                            THEN 0 ELSE 1
                        END) AS good_standing,
                        SUM(CASE
                            WHEN psa_mem_stat IN ('InArrears', 'In Arrears', 'Delinquent')
                            THEN 1 ELSE 0
                        END) AS not_good_standing
                    FROM member WITH (NOLOCK)
                    WHERE mem_stat = 'Active'
                    AND mem_last_name NOT IN ('Invalid', 'TEST')
                    AND psa_chapter_code = ?
                    AND psa_mem_type <> 'OT'
                    OPTION (RECOMPILE, MAXDOP 1)
                SQL,
                [$this->selectedChapter]
            );

            $this->summary['good_standing'] = (int) ($standing->good_standing ?? 0);
            $this->summary['not_good_standing'] = (int) ($standing->not_good_standing ?? 0);
        } catch (\Throwable) {
        }
    }

    protected function resetSummary(): void
    {
        $this->selectedChapter = null;
        $this->summary = [
            'total_members' => 0,
            'regular_members' => 0,
            'trainee_members' => 0,
            'life_members' => 0,
            'good_standing' => null,
            'not_good_standing' => null,
        ];
    }
}
