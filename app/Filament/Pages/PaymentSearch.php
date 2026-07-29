<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use BackedEnum;
use Filament\Pages\Page;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

use Illuminate\Database\Eloquent\Builder;

use App\Filament\Resources\Payments\Tables\PaymentsTable;

class PaymentSearch extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected string $view = 'filament.pages.payment-search';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Payment History';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Payments';
    }

    public string $searchBy = 'payment_ref_no';

    public ?string $searchValue = null;

    public bool $searched = false;

    public function search(): void
    {
        $this->searched = true;

        $this->resetTable();
    }

    public function resetSearch(): void
    {
        $this->searched = false;

        $this->searchValue = null;

        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return PaymentsTable::configure($table)
            ->query($this->getQuery());
    }

    protected function getQuery(): Builder
    {
        $query = Payment::query();

        if (! $this->searched) {

            return $query->whereRaw('1 = 0');
        }

        return match ($this->searchBy) {

            'payment_ref_no' =>

                $query->where(
                    'payment_ref_no',
                    'like',
                    "%{$this->searchValue}%"
                ),

            'or_no' =>

                $query->where(
                    'or_no',
                    'like',
                    "%{$this->searchValue}%"
                ),

            'member_id_no' =>

                $query->whereHas(
                    'member',
                    fn ($q) => $q->where(
                        'member_id_no',
                        'like',
                        "%{$this->searchValue}%"
                    )
                ),

            'last_name' =>

                $query->whereHas(
                    'member',
                    fn ($q) => $q->where(
                        'mem_last_name',
                        'like',
                        "%{$this->searchValue}%"
                    )
                ),

            default => $query,
        };
    }

    public function getSearchPlaceholderProperty(): string
    {
        return match ($this->searchBy) {

            'payment_ref_no' => 'Example: P260000123',

            'or_no' => 'Example: 012345',

            'member_id_no' => 'Example: 000123',

            'last_name' => 'Example: REYES',

            default => '',
        };
    }
}