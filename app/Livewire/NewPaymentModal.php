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

class NewPaymentModal extends Component
{
    public const PAYMENT_TYPES = ['Cash', 'Check', 'Bank Transfer'];

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

    #[Locked]
    public array $transactionItems = [];

    #[Locked]
    public array $availableItems = [];

    #[Locked]
    public bool $itemsLoaded = false;

    public array $selected = [];

    public array $amounts = [];

    public function mount(string|int $memberId): void
    {
        $this->authorizePayment();

        $member = Member::query()
            ->select([
                'member_id_no',
                'mem_last_name',
                'mem_first_name',
                'psa_mem_type',
            ])
            ->findOrFail($memberId);

        $this->memberId = (string) $member->member_id_no;

        $this->memberName = trim(
            "{$member->mem_last_name}, {$member->mem_first_name}"
        );

        $this->fiscalYear = (string) date('Y');

        $this->paymentDate = now()->toDateString();
    }

    public function initializeItems(): void
    {
        $this->authorizePayment();

        if ($this->itemsLoaded) {
            return;
        }

        $this->loadTransactionItems();
        $this->itemsLoaded = true;
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

    protected function loadTransactionItems(?Member $member = null): void
    {
        $member ??= Member::query()
            ->select([
                'member_id_no',
                'mem_last_name',
                'mem_first_name',
                'psa_mem_type',
            ])
            ->find($this->memberId);

        if (! $member || blank($this->fiscalYear)) {
            $this->availableItems = [];

            return;
        }

        $items = PaymentManager::getTransactionItems(
            $member,
            (int) $this->fiscalYear
        )->toArray();

        $currentItems = collect($items)
            ->values()
            ->map(fn (array $item, int $index) => $item + [
                '_key' => $this->getItemKey($item, $index),
            ])
            ->all();

        $this->availableItems = $currentItems;

        $allItems = collect($this->transactionItems)->keyBy('_key');

        foreach ($currentItems as $item) {
            $key = $item['_key'];

            $allItems->put($key, $item);

            if (! array_key_exists($key, $this->amounts)) {
                $this->amounts[$key] = $this->balanceOf($item);
            }
        }

        $this->transactionItems = $allItems->values()->all();
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
        $fiscalYear = (string) (
            $item['fiscal_year'] ?? $this->fiscalYear
        );

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
                    $fiscalYear,
                    $item['item_details'] ?? null,
                    $item['item_amount'] ?? null,
                    $index,
                ]) ?: (string) $index
            );

        return preg_replace(
            '/[^A-Za-z0-9_\-]/',
            '_',
            $fiscalYear . '_' . $key
        );
    }

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
                'fiscal_year' => (string) (
                    $item['fiscal_year'] ?? $this->fiscalYear
                ),
                'amount_paying' => round(
                    (float) ($this->amounts[$key] ?? 0),
                    2
                ),
            ];
        }

        return $lines;
    }

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

        $this->selected = [];
        $this->amounts = [];
        $this->transactionItems = [];
        $this->availableItems = [];

        $this->loadTransactionItems();

        Notification::make()
            ->title('Payment Successfully Posted')
            ->body("Payment Reference: {$reference}")
            ->success()
            ->persistent()
            ->send();

        $this->dispatch(
            'payment-posted',
            reference: $reference,
            memberId: $this->memberId
        );
    }

    protected function rules(): array
    {
        return [
            'fiscalYear' => ['required'],
            'paymentDate' => ['required', 'date'],
            'paymentType' => [
                'required',
                Rule::in(self::PAYMENT_TYPES),
            ],
            'orNo' => ['nullable', 'max:18'],
            'checkNo' => [
                'nullable',
                'required_if:paymentType,Check',
                'max:18',
            ],
            'bankNameCode' => [
                'nullable',
                'required_if:paymentType,Check,Bank Transfer',
                'max:50',
            ],
            'bankReferenceNo' => [
                'nullable',
                'required_if:paymentType,Bank Transfer',
                'max:50',
            ],
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

        return DB::transaction(function () use (
            $lines,
            $paymentType
        ): string {

            $member = Member::query()
                ->select([
                    'member_id_no',
                    'mem_last_name',
                    'mem_first_name',
                    'psa_mem_type',
                ])
                ->where('member_id_no', $this->memberId)
                ->lockForUpdate()
                ->first();

            if (! $member) {
                throw ValidationException::withMessages([
                    'selected' => 'The member could not be found.',
                ]);
            }

            $itemKey = static fn ($itemCode, $tranCode, $fiscalYear): string =>
                $fiscalYear . '|' . $tranCode . '|' . $itemCode;

            $itemCodes = array_values(array_unique(array_column($lines, 'item_code')));
            $tranCodes = array_values(array_unique(array_column($lines, 'tran_code')));
            $fiscalYears = array_values(array_unique(array_column($lines, 'fiscal_year')));

            $definitions = TransactionTypeItem::query()
                ->select([
                    'item_code',
                    'tran_code',
                    'fiscal_year',
                    'item_amount',
                ])
                ->whereIn('item_code', $itemCodes)
                ->whereIn('tran_code', $tranCodes)
                ->whereIn('fiscal_year', $fiscalYears)
                ->where('charge_code', $member->psa_mem_type)
                ->where('stat', 1)
                ->get()
                ->keyBy(fn ($item): string => $itemKey(
                    $item->item_code,
                    $item->tran_code,
                    $item->fiscal_year
                ));

            $paidAmounts = DB::table('payment_items as pi')
                ->join(
                    'payments as p',
                    'p.payment_ref_no',
                    '=',
                    'pi.payment_ref_no'
                )
                ->select([
                    'pi.item_code',
                    'pi.tran_code',
                    'pi.fiscal_year',
                ])
                ->selectRaw(
                    'SUM(COALESCE(pi.amount_paid, pi.amount_due)) AS total_paid'
                )
                ->where('pi.member_id_no', $member->member_id_no)
                ->whereIn('pi.item_code', $itemCodes)
                ->whereIn('pi.tran_code', $tranCodes)
                ->whereIn('pi.fiscal_year', $fiscalYears)
                ->where('pi.stat', 1)
                ->where('p.stat', 1)
                ->groupBy(
                    'pi.item_code',
                    'pi.tran_code',
                    'pi.fiscal_year'
                )
                ->get()
                ->keyBy(fn ($item): string => $itemKey(
                    $item->item_code,
                    $item->tran_code,
                    $item->fiscal_year
                ));

            $allocations = [];
            $totalPayment = 0;
            $seenItems = [];

            foreach ($lines as $line) {
                $itemCode = $line['item_code'];
                $tranCode = $line['tran_code'];
                $fiscalYear = $line['fiscal_year'];

                if (
                    $itemCode === ''
                    || $tranCode === ''
                    || $fiscalYear === ''
                ) {
                    throw ValidationException::withMessages([
                        'selected' => 'One or more selected items have invalid information.',
                    ]);
                }

                $uniqueKey = $itemKey($itemCode, $tranCode, $fiscalYear);

                if (isset($seenItems[$uniqueKey])) {
                    throw ValidationException::withMessages([
                        'selected' => "Duplicate transaction item detected: {$itemCode}.",
                    ]);
                }

                $seenItems[$uniqueKey] = true;

                $definition = $definitions->get($uniqueKey);

                if (! $definition) {
                    throw ValidationException::withMessages([
                        'selected' => "Transaction item {$itemCode} is no longer available.",
                    ]);
                }

                $originalAmount = round(
                    (float) $definition->item_amount,
                    2
                );

                $previouslyPaid = (float) (
                    $paidAmounts->get($uniqueKey)?->total_paid ?? 0
                );

                $balance = round(
                    max($originalAmount - $previouslyPaid, 0),
                    2
                );

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

                'check_no' => $paymentType === 'Check'
                    ? $this->checkNo
                    : '',

                'check_acct_no' => '',
                'check_name' => '',

                'bank_name_code' => in_array(
                    $paymentType,
                    ['Check', 'Bank Transfer'],
                    true
                )
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
                    (string) (
                        auth()->user()?->username
                        ?? auth()->id()
                        ?? ''
                    ),
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
