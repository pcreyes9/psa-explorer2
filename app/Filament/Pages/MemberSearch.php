<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberSearch extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.member-search';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    public ?string $searchInput = '';

    public static function getNavigationGroup(): ?string
    {
        return 'Membership';
    }

    public static function getNavigationLabel(): string
    {
        return 'Member Search';
    }

    public function searchMembers(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->recordUrl(
                fn (Member $record): string => MemberResource::getUrl('view', [
                    'record' => $record,
                ])
            )
            ->columns([
                TextColumn::make('member_id_no')
                    ->label('Member ID')
                    ->sortable(),

                TextColumn::make('mem_last_name')
                    ->label('Last Name')
                    ->sortable(),

                TextColumn::make('mem_first_name')
                    ->label('First Name')
                    ->sortable(),

                TextColumn::make('psa_chapter_code')
                    ->label('Chapter'),

                TextColumn::make('psa_mem_stat')
                    ->label('Status')
                    ->badge(),
            ])
            ->emptyStateHeading('Search for a member')
            ->emptyStateDescription('Enter a Member ID or Last Name above.');
    }

    protected function getQuery(): Builder
    {
        $search = trim($this->searchInput);

        $query = Member::query()
            ->select([
                'member_id_no',
                'mem_last_name',
                'mem_first_name',
                'psa_chapter_code',
                'psa_mem_stat',
            ]);

        if (blank($search)) {
            return $query->whereRaw('1 = 0');
        }

        if (is_numeric($search)) {
            return $query->where('member_id_no', $search);
        }

        return $query
            ->where('mem_last_name', 'like', $search . '%')
            ->orderBy('mem_last_name')
            ->orderBy('mem_first_name');
    }
}