<?php

namespace App\Filament\Pages;

use App\Models\Member;
use App\Models\TransactionTypeItem;
use App\Support\PaymentManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class NewPayment extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.pages.new-payment';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    public ?array $data = [];

    public array $transactionItems = [];

    /**
     * Transaction item codes selected in the modal.
     */
    public array $selectedTransactionItems = [];

    /**
     * Items included in the payment table.
     *
     * Each item uses a unique array key.
     */
    public array $selectedItems = [];

    public float $totalAmount = 0;

    public ?Member $selectedMember = null;

    public bool $openItemsModal = false;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('payment_create') ?? false;
    }

    public function getHeading(): string
    {
        return '';
    }

    public function mount(): void
    {
        $this->form->fill([
            'fiscal_year' => date('Y'),
        ]);

        $memberId = request()->query('member');

        if ($memberId) {
            $this->form->fill([
                'member_id_no' => $memberId,
                'fiscal_year' => date('Y'),
            ]);

            $this->loadTransactionItems();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([

                        Select::make('member_id_no')
                            ->label('Member')
                            ->searchable()
                            ->live()
                            ->placeholder('Search Member ID or Last Name')
                            ->getSearchResultsUsing(
                                function (string $search): array {
                                    return Member::query()
                                        ->select([
                                            'member_id_no',
                                            'mem_last_name',
                                            'mem_first_name',
                                        ])
                                        ->where(function ($query) use ($search) {
                                            $query
                                                ->where(
                                                    'member_id_no',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'mem_last_name',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        })
                                        ->orderBy('mem_last_name')
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn ($member) => [
                                            $member->member_id_no =>
                                                "{$member->member_id_no} - {$member->mem_last_name}, {$member->mem_first_name}",
                                        ])
                                        ->toArray();
                                }
                            )
                            ->getOptionLabelUsing(
                                function ($value): ?string {
                                    if (blank($value)) {
                                        return null;
                                    }

                                    $member = Member::query()
                                        ->select([
                                            'member_id_no',
                                            'mem_last_name',
                                            'mem_first_name',
                                        ])
                                        ->find($value);

                                    if (! $member) {
                                        return null;
                                    }

                                    return "{$member->member_id_no} - {$member->mem_last_name}, {$member->mem_first_name}";
                                }
                            )
                            ->afterStateUpdated(function ($state): void {
                                /*
                                 * A different member means a different payment.
                                 * Clear the previous member's selected items.
                                 */
                                $this->resetTransactionSelection();

                                $this->data['member_id_no'] = $state;

                                $this->loadTransactionItems();
                            })
                            ->columnSpan(3),

                        Select::make('fiscal_year')
                            ->label('Fiscal Year')
                            ->options(
                                TransactionTypeItem::query()
                                    ->distinct()
                                    ->orderByDesc('fiscal_year')
                                    ->pluck('fiscal_year', 'fiscal_year')
                                    ->toArray()
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state): void {
                                /*
                                 * Preserve the payment table when changing
                                 * the fiscal year.
                                 */
                                $this->data['fiscal_year'] = $state;

                                /*
                                 * Clear only the modal selection.
                                 * Do not clear selectedItems or totalAmount.
                                 */
                                $this->selectedTransactionItems = [];

                                $this->loadTransactionItems();

                                /*
                                 * Restore matching codes from existing
                                 * payment items into the modal selection.
                                 */
                                $this->restoreModalSelection();
                            })
                            ->columnSpan(1),

                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Modal action for selecting transaction items.
     */
    public function transactionItemsAction(): Action
    {
        return Action::make('transactionItems')
            ->label('Transaction Items')
            ->icon(Heroicon::OutlinedPlus)
            ->modalHeading('Transaction Items')
            ->modalWidth('7xl')
            ->schema([
                \Filament\Schemas\Components\View::make(
                    'filament.payments.transaction-items-modal'
                ),
            ])
            ->action(function (): void {
                /*
                 * Build a new table from the current modal selection,
                 * while preserving Amount Paying for existing rows.
                 *
                 * Items from previous fiscal years remain in the table
                 * even when they are not available in the current modal.
                 */
                $newSelectedItems = $this->selectedItems;

                foreach ($this->transactionItems as $index => $item) {
                    $itemCode = (string) ($item['item_code'] ?? '');

                    if (
                        $itemCode === ''
                        || ! in_array(
                            $itemCode,
                            $this->selectedTransactionItems
                        )
                    ) {
                        continue;
                    }

                    $key = $this->getSelectedItemKey($item, $index);

                    $amountDue = (float) ($item['item_amount'] ?? 0);

                    $amountPaying = isset($this->selectedItems[$key])
                        ? (float) (
                            $this->selectedItems[$key]['amount_paying']
                            ?? $amountDue
                        )
                        : $amountDue;

                    $newSelectedItems[$key] = [
                        'fiscal_year' => $item['fiscal_year']
                            ?? $this->data['fiscal_year']
                            ?? null,

                        'item_code' => $itemCode,

                        'description' => $item['item_details'] ?? '',

                        'amount_due' => $amountDue,

                        'amount_paying' => $amountPaying,
                    ];
                }

                $this->selectedItems = $newSelectedItems;

                $this->restoreModalSelection();

                $this->calculateTotal();
            })
            ->disabled(empty($this->transactionItems));
    }

    /**
     * Load available transaction items for the current member
     * and selected fiscal year.
     */
    public function loadTransactionItems(): void
    {
        $memberId = $this->data['member_id_no'] ?? null;
        $fiscalYear = $this->data['fiscal_year'] ?? null;

        if (! $memberId || ! $fiscalYear) {
            $this->selectedMember = null;
            $this->transactionItems = [];

            return;
        }

        $this->selectedMember = Member::find($memberId);

        if (! $this->selectedMember) {
            $this->transactionItems = [];

            return;
        }

        $this->transactionItems = PaymentManager::getTransactionItems(
            $this->selectedMember,
            (int) $fiscalYear
        )->toArray();
    }

    /**
     * Clear payment selections when changing members.
     */
    protected function resetTransactionSelection(): void
    {
        $this->transactionItems = [];
        $this->selectedTransactionItems = [];
        $this->selectedItems = [];
        $this->totalAmount = 0;
        $this->selectedMember = null;
    }

    /**
     * Identify MEMF using the transaction code.
     */
    protected function isMemfItem(array $item): bool
    {
        return strtoupper(
            trim((string) ($item['tran_code'] ?? ''))
        ) === 'MEMF';
    }

    /**
     * Generate a stable key for each transaction item.
     */
    protected function getSelectedItemKey(
        array $item,
        int $index
    ): string {
        $itemCode = (string) ($item['item_code'] ?? 'ITEM');

        $identity = $item['id']
            ?? $item['transaction_type_item_id']
            ?? $item['item_id']
            ?? null;

        if ($identity !== null) {
            return $itemCode . '_' . $identity;
        }

        return $itemCode . '_' . md5(
            json_encode([
                $item['tran_code'] ?? null,
                $item['fiscal_year'] ?? null,
                $item['item_details'] ?? null,
                $item['item_amount'] ?? null,
                $index,
            ]) ?: (string) $index
        );
    }

    /**
     * Select or deselect every item whose tran_code is MEMF.
     */
    public function toggleMemfItems(): void
    {
        $memfCodes = collect($this->transactionItems)
            ->filter(fn ($item) => $this->isMemfItem($item))
            ->pluck('item_code')
            ->filter()
            ->map(fn ($code) => (string) $code)
            ->unique()
            ->values()
            ->all();

        if (empty($memfCodes)) {
            return;
        }

        $allSelected = collect($memfCodes)->every(
            fn ($code) => in_array(
                $code,
                $this->selectedTransactionItems
            )
        );

        if ($allSelected) {
            $this->selectedTransactionItems = array_values(
                array_diff(
                    $this->selectedTransactionItems,
                    $memfCodes
                )
            );

            return;
        }

        $this->selectedTransactionItems = array_values(
            array_unique(
                array_merge(
                    $this->selectedTransactionItems,
                    $memfCodes
                )
            )
        );
    }

    /**
     * Select or deselect an individual transaction item.
     */
    public function toggleTransactionItem(string $itemCode): void
    {
        if (in_array($itemCode, $this->selectedTransactionItems)) {
            $this->selectedTransactionItems = array_values(
                array_filter(
                    $this->selectedTransactionItems,
                    fn ($code) => $code !== $itemCode
                )
            );

            return;
        }

        $this->selectedTransactionItems[] = $itemCode;

        $this->selectedTransactionItems = array_values(
            array_unique($this->selectedTransactionItems)
        );
    }

    /**
     * Restore modal selection from existing payment rows that match
     * the currently available transaction items.
     */
    protected function restoreModalSelection(): void
    {
        $existingCodes = collect($this->selectedItems)
            ->pluck('item_code')
            ->filter()
            ->map(fn ($code) => (string) $code)
            ->unique()
            ->values()
            ->all();

        $availableCodes = collect($this->transactionItems)
            ->pluck('item_code')
            ->filter()
            ->map(fn ($code) => (string) $code)
            ->unique()
            ->values()
            ->all();

        $this->selectedTransactionItems = array_values(
            array_intersect($existingCodes, $availableCodes)
        );
    }

    /**
     * Remove one selected payment item using its unique key.
     */
    public function removeItem(string $key): void
    {
        unset($this->selectedItems[$key]);

        $this->restoreModalSelection();

        $this->calculateTotal();
    }

    /**
     * Recalculate the payment total.
     */
    public function calculateTotal(): void
    {
        $this->totalAmount = (float) collect($this->selectedItems)
            ->sum(
                fn ($item) => (float) (
                    $item['amount_paying'] ?? 0
                )
            );
    }

    /**
     * Temporary debugging method.
     * Replace with actual payment posting logic when ready.
     */
    public function postPayment(): void
    {
        dd([
            'member' => $this->selectedMember,
            'items' => $this->selectedItems,
            'total' => $this->totalAmount,
        ]);
    }
}
