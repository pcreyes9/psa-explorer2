<?php

namespace App\Filament\Pages;

use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\TransactionTypeItem;
use App\Support\PaymentManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    public array $selectedTransactionItems = [];

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
            'payment_date' => now()->toDateString(),
            'payment_type' => 'Cash',
        ]);

        $memberId = request()->query('member');

        if ($memberId) {
            $this->form->fill([
                'member_id_no' => $memberId,
                'fiscal_year' => date('Y'),
                'payment_date' => now()->toDateString(),
                'payment_type' => 'Cash',
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
                                $this->resetTransactionSelection();

                                $this->data['member_id_no'] = $state;

                                $this->loadTransactionItems();
                            })
                            ->columnSpan(3),

                        Select::make('fiscal_year')
                            ->label('Fiscal Year')
                            ->options(
                                TransactionTypeItem::query()
                                    ->where('stat', 1)
                                    ->distinct()
                                    ->orderByDesc('fiscal_year')
                                    ->pluck('fiscal_year', 'fiscal_year')
                                    ->toArray()
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state): void {
                                $this->data['fiscal_year'] = $state;

                                $this->selectedTransactionItems = [];

                                $this->loadTransactionItems();

                                $this->restoreModalSelection();
                            })
                            ->columnSpan(1),

                    ]),

                Grid::make(3)
                    ->schema([

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now()->toDateString())
                            ->native(false),

                        Select::make('payment_type')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Check' => 'Check',
                                'Bank Transfer' => 'Bank Transfer',
                            ])
                            ->required()
                            ->default('Cash')
                            ->live()
                            ->afterStateUpdated(function ($state): void {

                                if ($state !== 'Check') {
                                    $this->data['check_no'] = '';
                                }

                                if (! in_array(
                                    $state,
                                    ['Check', 'Bank Transfer'],
                                    true
                                )) {
                                    $this->data['bank_name_code'] = '';
                                }

                                if ($state !== 'Bank Transfer') {
                                    $this->data['bank_reference_no'] = '';
                                }
                            }),

                        TextInput::make('or_no')
                            ->label('Official Receipt Number')
                            ->maxLength(18),

                        TextInput::make('check_no')
                            ->label('Check Number')
                            ->visible(
                                fn ($get): bool =>
                                    $get('payment_type') === 'Check'
                            )
                            ->required(
                                fn ($get): bool =>
                                    $get('payment_type') === 'Check'
                            )
                            ->maxLength(18),

                        TextInput::make('bank_name_code')
                            ->label('Bank Name')
                            ->visible(
                                fn ($get): bool => in_array(
                                    $get('payment_type'),
                                    ['Check', 'Bank Transfer'],
                                    true
                                )
                            )
                            ->required(
                                fn ($get): bool => in_array(
                                    $get('payment_type'),
                                    ['Check', 'Bank Transfer'],
                                    true
                                )
                            )
                            ->maxLength(50),

                        TextInput::make('bank_reference_no')
                            ->label('Bank Transfer Reference Number')
                            ->visible(
                                fn ($get): bool =>
                                    $get('payment_type') === 'Bank Transfer'
                            )
                            ->required(
                                fn ($get): bool =>
                                    $get('payment_type') === 'Bank Transfer'
                            )
                            ->maxLength(50),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->maxLength(50)
                            ->columnSpanFull(),

                    ]),

            ])
            ->statePath('data');
    }

    public function transactionItemsAction(): Action
    {
        return Action::make('transactionItems')
            ->label('Transaction Items')
            ->icon(Heroicon::OutlinedPlus)
            ->modalHeading('Transaction Items')
            ->modalWidth('7xl')
            ->schema([
                View::make('filament.payments.transaction-items-modal'),
            ])
            ->action(function (): void {

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

                    $amountDue = (float) (
                        $item['original_amount']
                        ?? $item['item_amount']
                        ?? 0
                    );

                    $balance = (float) (
                        $item['balance']
                        ?? $amountDue
                    );

                    if (isset($this->selectedItems[$key])) {

                        $amountPaying = (float) (
                            $this->selectedItems[$key]['amount_paying']
                            ?? $balance
                        );

                        $amountPaying = min(
                            max($amountPaying, 0),
                            $balance
                        );

                    } else {

                        $amountPaying = (float) (
                            $item['amount_paying']
                            ?? $balance
                        );
                    }

                    $newSelectedItems[$key] = [
                        'fiscal_year' => $item['fiscal_year']
                            ?? $this->data['fiscal_year']
                            ?? null,

                        'item_code' => $itemCode,

                        'tran_code' => $item['tran_code'] ?? null,

                        'description' => $item['item_details'] ?? '',

                        'amount_due' => $amountDue,

                        'previously_paid' => (float) (
                            $item['previously_paid'] ?? 0
                        ),

                        'balance' => $balance,

                        'amount_paying' => $amountPaying,
                    ];
                }

                $this->selectedItems = $newSelectedItems;

                $this->restoreModalSelection();

                $this->calculateTotal();
            })
            ->disabled(empty($this->transactionItems));
    }

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

    protected function resetTransactionSelection(): void
    {
        $this->transactionItems = [];

        $this->selectedTransactionItems = [];

        $this->selectedItems = [];

        $this->totalAmount = 0;

        $this->selectedMember = null;
    }

    protected function isMemfItem(array $item): bool
    {
        return strtoupper(
            trim((string) ($item['tran_code'] ?? ''))
        ) === 'MEMF';
    }

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

    public function removeItem(string $key): void
    {
        if (! isset($this->selectedItems[$key])) {
            return;
        }

        $itemCode = (string) $this->selectedItems[$key]['item_code'];

        unset($this->selectedItems[$key]);

        $this->selectedTransactionItems = array_values(
            array_filter(
                $this->selectedTransactionItems,
                fn ($code) => (string) $code !== $itemCode
            )
        );

        $this->totalAmount = round(
            (float) collect($this->selectedItems)
                ->sum('amount_paying'),
            2
        );
    }

    public function updatedSelectedItems($value, $key): void
    {
        if (! str_ends_with($key, '.amount_paying')) {
            return;
        }

        $itemKey = substr(
            $key,
            0,
            -strlen('.amount_paying')
        );

        if (! isset($this->selectedItems[$itemKey])) {
            return;
        }

        $balance = (float) (
            $this->selectedItems[$itemKey]['balance']
            ?? $this->selectedItems[$itemKey]['amount_due']
            ?? 0
        );

        $amountPaying = is_numeric($value)
            ? (float) $value
            : 0;

        $this->selectedItems[$itemKey]['amount_paying'] = round(
            min(max($amountPaying, 0), $balance),
            2
        );

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->totalAmount = round(
            (float) collect($this->selectedItems)
                ->sum(fn ($item) => (float) (
                    $item['amount_paying'] ?? 0
                )),
            2
        );
    }

    protected function getPaymentName(Member $member): string
    {
        return trim(
            strtoupper(
                trim((string) $member->mem_last_name)
                . ', '
                . trim((string) $member->mem_first_name)
            )
        );
    }

    public function postPayment(): void
    {
        $paymentData = $this->form->getState();

        $memberId = $paymentData['member_id_no'] ?? null;

        if (blank($memberId)) {
            throw ValidationException::withMessages([
                'data.member_id_no' => 'Please select a member.',
            ]);
        }

        if (empty($this->selectedItems)) {
            throw ValidationException::withMessages([
                'selectedItems' => 'Please select at least one transaction item.',
            ]);
        }

        $member = Member::find($memberId);

        if (! $member) {
            throw ValidationException::withMessages([
                'data.member_id_no' => 'The selected member could not be found.',
            ]);
        }

        $paymentType = $paymentData['payment_type'] ?? null;

        if (! in_array(
            $paymentType,
            ['Cash', 'Check', 'Bank Transfer'],
            true
        )) {
            throw ValidationException::withMessages([
                'data.payment_type' => 'Please select a valid payment method.',
            ]);
        }

        if ($paymentType === 'Check') {

            if (blank($paymentData['check_no'] ?? null)) {
                throw ValidationException::withMessages([
                    'data.check_no' => 'Check number is required.',
                ]);
            }

            if (blank($paymentData['bank_name_code'] ?? null)) {
                throw ValidationException::withMessages([
                    'data.bank_name_code' => 'Bank name is required.',
                ]);
            }
        }

        if ($paymentType === 'Bank Transfer') {

            if (blank($paymentData['bank_name_code'] ?? null)) {
                throw ValidationException::withMessages([
                    'data.bank_name_code' => 'Bank name is required.',
                ]);
            }

            if (blank($paymentData['bank_reference_no'] ?? null)) {
                throw ValidationException::withMessages([
                    'data.bank_reference_no' => 'Bank reference number is required.',
                ]);
            }
        }

        if (blank($paymentData['payment_date'] ?? null)) {
            throw ValidationException::withMessages([
                'data.payment_date' => 'Payment date is required.',
            ]);
        }

        $paymentReference = null;

        DB::transaction(function () use (
            $paymentData,
            $member,
            &$paymentReference
        ) {

            $allocations = [];

            $totalPayment = 0;

            $seenItems = [];

            foreach ($this->selectedItems as $selectedItem) {

                $itemCode = (string) ($selectedItem['item_code'] ?? '');

                $tranCode = (string) ($selectedItem['tran_code'] ?? '');

                $fiscalYear = (string) ($selectedItem['fiscal_year'] ?? '');

                if (
                    $itemCode === ''
                    || $tranCode === ''
                    || $fiscalYear === ''
                ) {
                    throw ValidationException::withMessages([
                        'selectedItems' => 'One or more selected items have invalid information.',
                    ]);
                }

                $uniqueKey = $fiscalYear . '|' . $tranCode . '|' . $itemCode;

                if (isset($seenItems[$uniqueKey])) {
                    throw ValidationException::withMessages([
                        'selectedItems' => "Duplicate transaction item detected: {$itemCode}.",
                    ]);
                }

                $seenItems[$uniqueKey] = true;

                $definition = TransactionTypeItem::query()
                    ->where('item_code', $itemCode)
                    ->where('tran_code', $tranCode)
                    ->where('fiscal_year', $fiscalYear)
                    ->where('charge_code', $member->psa_mem_type)
                    ->where('stat', 1)
                    ->lockForUpdate()
                    ->first();

                if (! $definition) {
                    throw ValidationException::withMessages([
                        'selectedItems' => "Transaction item {$itemCode} is no longer available.",
                    ]);
                }

                $originalAmount = round(
                    (float) $definition->item_amount,
                    2
                );

                $previouslyPaid = (float) DB::table('payment_items as pi')
                    ->join(
                        'payments as p',
                        'p.payment_ref_no',
                        '=',
                        'pi.payment_ref_no'
                    )
                    ->where('pi.member_id_no', $member->member_id_no)
                    ->where('pi.fiscal_year', $fiscalYear)
                    ->where('pi.item_code', $itemCode)
                    ->where('pi.tran_code', $tranCode)
                    ->where('pi.stat', 1)
                    ->where('p.stat', 1)
                    ->sum(DB::raw(
                        'COALESCE(pi.amount_paid, pi.amount_due)'
                    ));

                $balance = round(
                    max($originalAmount - $previouslyPaid, 0),
                    2
                );

                $amountPaying = round(
                    (float) ($selectedItem['amount_paying'] ?? 0),
                    2
                );

                if ($amountPaying <= 0) {
                    throw ValidationException::withMessages([
                        'selectedItems' => "The payment amount for {$itemCode} must be greater than zero.",
                    ]);
                }

                if ($amountPaying > $balance) {
                    throw ValidationException::withMessages([
                        'selectedItems' =>
                            "The payment amount for {$itemCode} exceeds its current outstanding balance of ₱"
                            . number_format($balance, 2)
                            . '. Please refresh your payment items.',
                    ]);
                }

                $allocations[] = [
                    'member_id_no' => $member->member_id_no,
                    'fiscal_year' => $fiscalYear,
                    'item_code' => $itemCode,
                    'tran_code' => $tranCode,
                    'mem_type' => $member->psa_mem_type,
                    'amount_due' => $originalAmount,
                    'amount_paid' => $amountPaying,
                    'stat' => 1,
                ];

                $totalPayment += $amountPaying;
            }

            $totalPayment = round($totalPayment, 2);

            if ($totalPayment <= 0) {
                throw ValidationException::withMessages([
                    'selectedItems' => 'The total payment must be greater than zero.',
                ]);
            }

            $paymentReference = PaymentManager::generateReference($member);

            Payment::create([

                'payment_ref_no' => $paymentReference,

                'member_id_no' => $member->member_id_no,

                'payment_name' => $this->getPaymentName($member),

                'payment_add' => '',

                'payment_total_amt' => $totalPayment,

                'payment_gross_amt' => $totalPayment,

                'payment_discount' => 0,

                'payment_discounted_amt' => $totalPayment,

                'payment_tax' => 0,

                'payment_tax_amt' => 0,

                'payment_type' => $paymentData['payment_type'],

                'payment_date' => $paymentData['payment_date'],

                'check_no' => $paymentType === 'Check'
                    ? ($paymentData['check_no'] ?? '')
                    : '',

                'check_acct_no' => '',

                'check_name' => '',

                'bank_name_code' => in_array(
                    $paymentType,
                    ['Check', 'Bank Transfer'],
                    true
                )
                    ? ($paymentData['bank_name_code'] ?? '')
                    : '',

                'epay_vendor_name_code' => '',

                'epay_acct_no' => '',

                'credit_card_type' => '',

                'credit_card_no' => '',

                'credit_card_exp' => null,

                'debit_card_type' => '',

                'debit_card_no' => '',

                'debit_card_exp' => '',

                'or_no' => $paymentData['or_no'] ?? '',

                'userid' => substr(
                    (string) (
                        auth()->user()?->username
                        ?? auth()->id()
                        ?? ''
                    ),
                    0,
                    20
                ),

                'overideby' => '',

                'remarks' => $paymentData['remarks'] ?? '',

                'stat' => 1,

                'bank_reference_no' => $paymentType === 'Bank Transfer'
                    ? ($paymentData['bank_reference_no'] ?? '')
                    : '',

            ]);

            foreach ($allocations as $allocation) {

                PaymentItem::create([
                    'payment_ref_no' => $paymentReference,
                    ...$allocation,
                ]);
            }

        }, 3);

        $this->selectedItems = [];

        $this->selectedTransactionItems = [];

        $this->totalAmount = 0;

        $this->loadTransactionItems();

        Notification::make()
            ->title('Payment Successfully Posted')
            ->body("Payment Reference: {$paymentReference}")
            ->success()
            ->persistent()
            ->send();
    }

}
