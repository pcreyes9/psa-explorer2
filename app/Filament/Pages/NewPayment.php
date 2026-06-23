<?php

namespace App\Filament\Pages;

use App\Models\Member;
use App\Models\TransactionTypeItem;
use App\Support\PaymentManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;

class NewPayment extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.pages.new-payment';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    // protected static ?string $navigationLabel = 'New Payment';

    public ?array $data = [];

    public array $transactionItems = [];

    public array $selectedTransactionItems = [];

    public array $selectedItems = [];

    public float $totalAmount = 0;
    public ?Member $selectedMember = null;

    public bool $openItemsModal = false;

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
    public function getHeading(): string
    {
        return '';
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
                            ->getSearchResultsUsing(function (string $search): array {

                                return Member::query()
                                    ->select([
                                        'member_id_no',
                                        'mem_last_name',
                                        'mem_first_name',
                                    ])
                                    ->where('member_id_no', 'like', "%{$search}%")
                                    ->orWhere('mem_last_name', 'like', "%{$search}%")
                                    ->orderBy('mem_last_name')
                                    // ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn ($member) => [
                                        $member->member_id_no =>
                                            "{$member->member_id_no} - {$member->mem_last_name}, {$member->mem_first_name}",
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {

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
                            })
                            ->afterStateUpdated(fn () => $this->loadTransactionItems())
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
                            ->afterStateUpdated(fn () => $this->loadTransactionItems())
                            ->columnSpan(1),

                    ]),
            ])
            ->statePath('data');
    }

    public function transactionItemsAction(): Action
    {
        return Action::make('transactionItems')
            ->label('Transaction Items')
            ->icon(Heroicon::OutlinedPlus)
            // ->color('gray')
            ->modalHeading('Transaction Items')
            ->modalWidth('7xl')
            ->schema([
                \Filament\Schemas\Components\View::make(
                    'filament.payment.transaction-items-modal'
                ),
            ])
            ->action(function () {

                foreach ($this->selectedTransactionItems as $itemCode) {

                    $item = collect($this->transactionItems)
                        ->firstWhere('item_code', $itemCode);

                    if (! $item) {
                        continue;
                    }

                    $this->selectedItems[$itemCode] = [
                        'fiscal_year' => $item['fiscal_year'],
                        'item_code' => $item['item_code'],
                        'description' => $item['item_details'],
                        'amount_due' => (float) $item['item_amount'],
                        'amount_paying' => (float) $item['item_amount'],
                    ];
                }

                $this->calculateTotal();

                $this->selectedTransactionItems = array_keys($this->selectedItems);
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

    public function removeItem(string $key): void
    {
        unset($this->selectedItems[$key]);

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->totalAmount = collect($this->selectedItems)
            ->sum('amount_paying');
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
    }

    public function postPayment(): void
    {
        dd([
            'member' => $this->selectedMember,
            'items' => $this->selectedItems,
            'total' => $this->totalAmount,
        ]);
    }
}
