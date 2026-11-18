<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

class PaymentSearch extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected string $view = 'filament.pages.payment-search';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Payment Search';
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
        $this->searchValue = trim((string) $this->searchValue);

        $this->validate([
            'searchBy' => ['required', Rule::in([
                'payment_ref_no',
                'or_no',
                'member_id_no',
                'last_name',
            ])],
            'searchValue' => ['required', 'string', 'max:100'],
        ]);

        $this->searched = true;
        $this->resetPage();
    }

    public function updatedSearchBy(): void
    {
        $this->searchValue = null;
        $this->searched = false;
        $this->resetPage();
    }

    public function resetSearch(): void
    {
        $this->searched = false;
        $this->searchValue = null;
        $this->resetPage();
        $this->resetValidation();
    }

    public function getSearchResultsProperty(): LengthAwarePaginator
    {
        return $this->getQuery()
            ->orderByDesc('payment_date')
            ->orderByDesc('payment_ref_no')
            ->paginate(10);
    }

    protected function getQuery(): Builder
    {
        $query = Payment::query()
            ->select([
                'payment_ref_no',
                'member_id_no',
                'payment_date',
                'or_no',
                'payment_type',
                'payment_total_amt',
                'payment_discounted_amt',
                'payment_name',
                'userid',
                'stat',
                'remarks',
            ])
            ->with([
                'paymentItems' => function ($query) {
                    $query->select([
                        'payment_ref_no',
                        'member_id_no',
                        'item_code',
                        'fiscal_year',
                        'tran_code',
                        'amount_due',
                    ]);
                },
                'paymentItems.transactionTypeItem' => function ($query) {
                    $query->select([
                        'item_code',
                        'item_details',
                        'fiscal_year',
                    ]);
                },
            ]);

        $value = trim((string) $this->searchValue);

        if (! $this->searched || $value === '') {
            return $query->whereRaw('1 = 0');
        }

        return match ($this->searchBy) {
            'payment_ref_no', 'or_no' => $query->where(
                $this->searchBy,
                'like',
                "%{$value}%"
            ),
            'member_id_no' => $query->where(
                'member_id_no',
                'like',
                "%{$value}%"
            ),
            'last_name' => $query->whereHas(
                'member',
                fn ($memberQuery) => $memberQuery->where(
                    'mem_last_name',
                    'like',
                    "%{$value}%"
                )
            ),
            default => $query->whereRaw('1 = 0'),
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
