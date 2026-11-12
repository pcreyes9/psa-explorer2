<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\TransactionTypeItem;
use App\Support\PaymentManager;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Payment form shown inside the "New Payment" modal of the member page.
 * The member is fixed (passed in from the page), so there is no member search.
 */
class NewPaymentModal extends Component
{
    public const PAYMENT_TYPES = ['Cash', 'Check', 'Bank Transfer'];

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    #[Locked]
    public string $memberId = '';

    #[Locked]
    public string $memberName = '';

    public string $fiscalYear = '';

    public string $paymentDate = '';

    public string $paymentType = 'Cash';

    public string $orNo = '';

    public string $checkNo = '';

    public string $bankNameCode = '';

    public string $bankReferenceNo = '';

    public string $remarks = '';

    /** Items available for this member and fiscal year (read-only for the browser). */
    #[Locked]
    public array $transactionItems = [];

    /** Keys of the items added to this payment (changed in the browser, synced with the next request). */
    public array $selected = [];

    /** Amount paying per item key (changed in the browser, synced with the next request). */
    public array $amounts = [];

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    */

    public function mount(string|int $memberId): void
    {
        $this->authorizePayment();

        $member = Member::query()->findOrFail($memberId);

        $this->memberId = (string) $member->member_id_no;

        $this->memberName = trim("{$member->mem_last_name}, {$member->mem_first_name}");

        $this->fiscalYear = (string) date('Y');

        $this->paymentDate = now()->toDateString();

        $this->loadTransactionItems($member);
    }

    public function render()
    {
        return view('livewire.new-payment-modal');
    }

    protected function authorizePayment(): void
    {
        abort_unless(auth()->user()?->can('payment_create'), 403);
    }

    #[Computed]
    public function fiscalYears(): array
    {
        return Cache::remember(
            'payments.fiscal_year_options',
            now()->addMinutes(10),
            fn (): array => TransactionTypeItem::query()
                ->where('stat', 1)
                ->distinct()
                ->orderByDesc('fiscal_year')
                ->pluck('fiscal_year')
                ->map(fn ($year) => (string) $year)
                ->all()
        );
    }

    public function updatedFiscalYear(): void
    {
        $this->authorizePayment();

        $this->loadTransactionItems();
    }

    /*
    |--------------------------------------------------------------------------
    | Items
    |--------------------------------------------------------------------------
    */

    protected function loadTransactionItems(?Member $member = null): void
    {
        $member ??= Member::query()->find($this->memberId);

        $this->selected = [];

        $this->amounts = [];

        if (! $member || blank($this->fiscalYear)) {
            $this->transactionItems = [];

            return;
        }

        $items = PaymentManager::getTransactionItems(
            $member,
            (int) $this->fiscalYear
        )->toArray();

        $this->transactionItems = collect($items)
            ->values()
            ->map(fn (array $item, int $index) => $item + [
                '_key' => $this->getItemKey($item, $index),
            ])
            ->all();

        // Pre-fill each amount with the outstanding balance.
        foreach ($this->transactionItems as $item) {
            $this->amounts[$item['_key']] = $this->balanceOf($item);
        }
    }

    protected function balanceOf(array $item): float
    {
        $amountDue = (float) (
            $item['original_amount']
            ?? $item['item_amount']
            ?? 0
        );

        return (float) ($item['balance'] ?? $amountDue);
    }

    protected function getItemKey(array $item, int $index): string
    {
        $itemCode = (string) ($item['item_code'] ?? 'ITEM');

        $identity = $item['id']
            ?? $item['transaction_type_item_id']
            ?? $item['item_id']
            ?? null;

        $key = $identity !== null
            ? $itemCode . '_' . $identity
            : $itemCode . '_' . md5(
                json_encode([
                    $item['tran_code'] ?? null,
                    $item['fiscal_year'] ?? null,
                    $item['item_details'] ?? null,
                    $item['item_amount'] ?? null,
                    $index,
                ]) ?: (string) $index
            );

        // Keys are used in wire:model paths, so keep them free of dots etc.
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $key);
    }

    /**
     * Builds the payment lines from the added item keys. Item details come from the
     * server-side list, never from the browser; only the amounts are user input
     * (and those are re-checked against the database when posting).
     */
    protected function buildSelection(): array
    {
        $items = collect($this->transactionItems)->keyBy('_key');

        $lines = [];

        foreach (array_unique(array_map('strval', $this->selected)) as $key) {

            $item = $items->get($key);

            if (! $item) {
                continue;
            }

            $lines[] = [
                'item_code' => (string) ($item['item_code'] ?? ''),
                'tran_code' => (string) ($item['tran_code'] ?? ''),
                'fiscal_year' => (string) ($item['fiscal_year'] ?? $this->fiscalYear),
                'amount_paying' => round((float) ($this->amounts[$key] ?? 0), 2),
            ];
        }

        return $lines;
    }

    /*
    |--------------------------------------------------------------------------
    | Post Payment
    |--------------------------------------------------------------------------
    */

    public function postPayment(): void
    {
        $this->authorizePayment();

        try {
            $reference = $this->processPayment();
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());

            Notification::make()
                ->title('Payment could not be posted')
                ->body(collect($e->errors())->flatten()->implode(' '))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        // Refresh balances and clear the ticked items.
        $this->loadTransactionItems();

        Notification::make()
            ->title('Payment Successfully Posted')
            ->body("Payment Reference: {$reference}")
            ->success()
            ->persistent()
            ->send();

        // The member page can listen for this to close the modal / refresh.
        $this->dispatch('payment-posted', reference: $reference, memberId: $this->memberId);
    }

    protected function rules(): array
    {
        return [
            'fiscalYear' => ['required'],
            'paymentDate' => ['required', 'date'],
            'paymentType' => ['required', Rule::in(self::PAYMENT_TYPES)],
            'orNo' => ['nullable', 'max:18'],
            'checkNo' => ['nullable', 'required_if:paymentType,Check', 'max:18'],
            'bankNameCode' => ['nullable', 'required_if:paymentType,Check,Bank Transfer', 'max:50'],
            'bankReferenceNo' => ['nullable', 'required_if:paymentType,Bank Transfer', 'max:50'],
            'remarks' => ['nullable', 'max:50'],
        ];
    }

    protected function messages(): array
    {
        return [
            'checkNo.required_if' => 'Check number is required.',
            'bankNameCode.required_if' => 'Bank name is required.',
            'bankReferenceNo.required_if' => 'Bank reference number is required.',
            'paymentDate.required' => 'Payment date is required.',
        ];
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

    /** Validates and saves the payment. Returns the payment reference. */
    protected function processPayment(): string
    {
        $this->resetErrorBag();

        $lines = $this->buildSelection();

        if (empty($lines)) {
            throw ValidationException::withMessages([
                'selected' => 'Please add at least one transaction item.',
            ]);
        }

        $this->validate();

        $paymentType = $this->paymentType;

        return DB::transaction(function () use ($lines, $paymentType): string {

            // Lock the member row first so two cashiers cannot post payments
            // for the same member at once and overpay an item.
            $member = Member::query()
                ->where('member_id_no', $this->memberId)
                ->lockForUpdate()
                ->first();

            if (! $member) {
                throw ValidationException::withMessages([
                    'selected' => 'The member could not be found.',
                ]);
            }

            $allocations = [];

            $totalPayment = 0;

            $seenItems = [];

            foreach ($lines as $line) {

                $itemCode = $line['item_code'];

                $tranCode = $line['tran_code'];

                $fiscalYear = $line['fiscal_year'];

                if ($itemCode === '' || $tranCode === '' || $fiscalYear === '') {
                    throw ValidationException::withMessages([
                        'selected' => 'One or more selected items have invalid information.',
                    ]);
                }

                $uniqueKey = $fiscalYear . '|' . $tranCode . '|' . $itemCode;

                if (isset($seenItems[$uniqueKey])) {
                    throw ValidationException::withMessages([
                        'selected' => "Duplicate transaction item detected: {$itemCode}.",
                    ]);
                }

                $seenItems[$uniqueKey] = true;

                // Always re-read the definition and previous payments from the
                // database; never trust amounts sent by the browser.
                $definition = TransactionTypeItem::query()
                    ->where('item_code', $itemCode)
                    ->where('tran_code', $tranCode)
                    ->where('fiscal_year', $fiscalYear)
                    ->where('charge_code', $member->psa_mem_type)
                    ->where('stat', 1)
                    ->first();

                if (! $definition) {
                    throw ValidationException::withMessages([
                        'selected' => "Transaction item {$itemCode} is no longer available.",
                    ]);
                }

                $originalAmount = round((float) $definition->item_amount, 2);

                $previouslyPaid = (float) DB::table('payment_items as pi')
                    ->join('payments as p', 'p.payment_ref_no', '=', 'pi.payment_ref_no')
                    ->where('pi.member_id_no', $member->member_id_no)
                    ->where('pi.fiscal_year', $fiscalYear)
                    ->where('pi.item_code', $itemCode)
                    ->where('pi.tran_code', $tranCode)
                    ->where('pi.stat', 1)
                    ->where('p.stat', 1)
                    ->sum(DB::raw('COALESCE(pi.amount_paid, pi.amount_due)'));

                $balance = round(max($originalAmount - $previouslyPaid, 0), 2);

                $amountPaying = $line['amount_paying'];

                if ($amountPaying <= 0) {
                    throw ValidationException::withMessages([
                        'selected' => "The payment amount for {$itemCode} must be greater than zero.",
                    ]);
                }

                if ($amountPaying > $balance) {
                    throw ValidationException::withMessages([
                        'selected' =>
                            "The payment amount for {$itemCode} exceeds its current outstanding balance of ₱"
                            . number_format($balance, 2)
                            . '.',
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
                    'selected' => 'The total payment must be greater than zero.',
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

                'payment_type' => $paymentType,

                'payment_date' => $this->paymentDate,

                'check_no' => $paymentType === 'Check' ? $this->checkNo : '',

                'check_acct_no' => '',

                'check_name' => '',

                'bank_name_code' => in_array($paymentType, ['Check', 'Bank Transfer'], true)
                    ? $this->bankNameCode
                    : '',

                'epay_vendor_name_code' => '',

                'epay_acct_no' => '',

                'credit_card_type' => '',

                'credit_card_no' => '',

                'credit_card_exp' => null,

                'debit_card_type' => '',

                'debit_card_no' => '',

                'debit_card_exp' => '',

                'or_no' => $this->orNo,

                'userid' => substr(
                    (string) (auth()->user()?->username ?? auth()->id() ?? ''),
                    0,
                    20
                ),

                'overideby' => '',

                'remarks' => $this->remarks,

                'stat' => 1,

                'bank_reference_no' => $paymentType === 'Bank Transfer'
                    ? $this->bankReferenceNo
                    : '',

            ]);

            foreach ($allocations as $allocation) {

                PaymentItem::create([
                    'payment_ref_no' => $paymentReference,
                    ...$allocation,
                ]);
            }

            return $paymentReference;

        }, 3);
    }
}