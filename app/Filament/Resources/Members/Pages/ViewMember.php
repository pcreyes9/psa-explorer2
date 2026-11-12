<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Models\MemberLedgerBalance;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload as FormFileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected string $view = 'filament.members.view-member';

    public bool $financialRecordsLoaded = false;

    public $financialPayments = [];

    public $financialArchives = [];

    public $financialBalances = [];

    public $cmePayments = [];

    public $financialLedger = [];

    public $advancePayments = [];

    public function loadFinancialRecords(): void
    {
        $memberId = $this->record->member_id_no;
        $memberType = $this->record->psa_mem_type;

        $this->financialPayments = $this->record
            ->payments()
            ->select([
                'payment_ref_no',
                'member_id_no',
                'payment_date',
                'or_no',
                'payment_type',
                'payment_total_amt',
                'payment_discounted_amt',
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
            ])
            ->orderByDesc('payment_date')
            ->get();

        $this->financialArchives = $this->record
            ->archivedPayments()
            ->select([
                'member_id_no',
                'payment_date',
                'or_no',
                'payment_type',
                'payment_total_amt',
                'userid',
                'remarks',
            ])
            ->orderByDesc('payment_date')
            ->get();

        $fiscalYears = range(2027, 2020);

        $feeComponents = DB::table('transaction_type_item as t')
            ->selectRaw("
                t.fiscal_year,
                CASE
                    WHEN t.item_code LIKE 'RM%' THEN 'RM'
                    WHEN t.item_code LIKE 'TM%' THEN 'TM'
                    WHEN t.item_code LIKE 'LM%' THEN 'LM'
                END AS member_type,
                CASE
                    WHEN RIGHT(t.item_code, 4) = 'MEMF' THEN 'MEMF'
                    WHEN RIGHT(t.item_code, 3) = 'DBF' THEN 'DBF'
                    WHEN RIGHT(t.item_code, 3) = 'LAF' THEN 'LAF'
                    WHEN RIGHT(t.item_code, 4) = 'WFSA' THEN 'WFSA'
                END AS component,
                SUM(t.item_amount) AS component_amount
            ")
            ->where('t.stat', 1)
            ->whereBetween('t.fiscal_year', [2020, 2027])
            ->where(function ($query) {
                $query->where('t.item_code', 'like', 'RM%')
                    ->orWhere('t.item_code', 'like', 'TM%')
                    ->orWhere('t.item_code', 'like', 'LM%');
            })
            ->where(function ($query) {
                $query->whereRaw(
                    "RIGHT(t.item_code, 4) IN ('MEMF', 'WFSA')"
                )->orWhereRaw(
                    "RIGHT(t.item_code, 3) IN ('DBF', 'LAF')"
                );
            })
            ->groupByRaw("
                t.fiscal_year,
                CASE
                    WHEN t.item_code LIKE 'RM%' THEN 'RM'
                    WHEN t.item_code LIKE 'TM%' THEN 'TM'
                    WHEN t.item_code LIKE 'LM%' THEN 'LM'
                END,
                CASE
                    WHEN RIGHT(t.item_code, 4) = 'MEMF' THEN 'MEMF'
                    WHEN RIGHT(t.item_code, 3) = 'DBF' THEN 'DBF'
                    WHEN RIGHT(t.item_code, 3) = 'LAF' THEN 'LAF'
                    WHEN RIGHT(t.item_code, 4) = 'WFSA' THEN 'WFSA'
                END
            ")
            ->get();

        $annualFees = [];

        foreach ($feeComponents as $component) {
            $year = (int) $component->fiscal_year;
            $type = $component->member_type;

            if (!isset($annualFees[$year][$type])) {
                $annualFees[$year][$type] = [
                    'standard_memf' => 0,
                    'standard_dbf' => 0,
                    'standard_laf' => 0,
                    'standard_wfsa' => 0,
                    'standard_annual_dues' => 0,
                ];
            }

            $amount = (float) $component->component_amount;

            switch ($component->component) {
                case 'MEMF':
                    $annualFees[$year][$type]['standard_memf'] += $amount;
                    break;

                case 'DBF':
                    $annualFees[$year][$type]['standard_dbf'] += $amount;
                    break;

                case 'LAF':
                    $annualFees[$year][$type]['standard_laf'] += $amount;
                    break;

                case 'WFSA':
                    $annualFees[$year][$type]['standard_wfsa'] += $amount;
                    break;
            }

            $annualFees[$year][$type]['standard_annual_dues'] += $amount;
        }

        $paymentRows = DB::table('payment_items as pi')
            ->join('payments as p', function ($join) {
                $join->on('p.payment_ref_no', '=', 'pi.payment_ref_no')
                    ->on('p.member_id_no', '=', 'pi.member_id_no');
            })
            ->where('pi.member_id_no', $memberId)
            ->where('pi.tran_code', 'MEMF')
            ->where('pi.stat', 1)
            ->where('p.stat', 1)
            ->whereBetween('pi.fiscal_year', [2020, 2027])
            ->where(function ($query) {
                $query->where('pi.item_code', 'like', 'RM%')
                    ->orWhere('pi.item_code', 'like', 'TM%')
                    ->orWhere('pi.item_code', 'like', 'LM%');
            })
            ->where(function ($query) {
                $query->whereRaw(
                    "RIGHT(pi.item_code, 4) IN ('MEMF', 'WFSA')"
                )->orWhereRaw(
                    "RIGHT(pi.item_code, 3) IN ('DBF', 'LAF')"
                );
            })
            ->selectRaw("
                pi.fiscal_year,

                SUM(CASE
                    WHEN RIGHT(pi.item_code, 4) = 'MEMF'
                    THEN pi.amount_due ELSE 0
                END) AS memf_paid,

                SUM(CASE
                    WHEN RIGHT(pi.item_code, 3) = 'DBF'
                    THEN pi.amount_due ELSE 0
                END) AS dbf_paid,

                SUM(CASE
                    WHEN RIGHT(pi.item_code, 3) = 'LAF'
                    THEN pi.amount_due ELSE 0
                END) AS laf_paid,

                SUM(CASE
                    WHEN RIGHT(pi.item_code, 4) = 'WFSA'
                    THEN pi.amount_due ELSE 0
                END) AS wfsa_paid,

                SUM(pi.amount_due) AS total_paid,

                COUNT(*) AS payment_item_count
            ")
            ->groupBy('pi.fiscal_year')
            ->get()
            ->keyBy('fiscal_year');

        $this->financialLedger = collect($fiscalYears)
            ->map(function ($year) use (
                $annualFees,
                $paymentRows,
                $memberType
            ) {
                $fee = $annualFees[$year][$memberType] ?? [
                    'standard_memf' => 0,
                    'standard_dbf' => 0,
                    'standard_laf' => 0,
                    'standard_wfsa' => 0,
                    'standard_annual_dues' => 0,
                ];

                $payment = $paymentRows->get($year);

                $standardMemf = (float) $fee['standard_memf'];
                $standardDbf = (float) $fee['standard_dbf'];
                $standardLaf = (float) $fee['standard_laf'];
                $standardWfsa = (float) $fee['standard_wfsa'];
                $standardDues = (float) $fee['standard_annual_dues'];

                $memfPaid = (float) ($payment->memf_paid ?? 0);
                $dbfPaid = (float) ($payment->dbf_paid ?? 0);
                $lafPaid = (float) ($payment->laf_paid ?? 0);
                $wfsaPaid = (float) ($payment->wfsa_paid ?? 0);
                $totalPaid = (float) ($payment->total_paid ?? 0);

                $balance = round($standardDues - $totalPaid, 2);

                if ($totalPaid == 0.0) {
                    $status = 'No Recorded Payment';
                } elseif (abs($balance) < 0.005) {
                    $status = 'Fully Paid';
                } elseif ($balance < 0) {
                    $status = 'Overpaid';
                } else {
                    $status = 'Partial Payment';
                }

                return (object) [
                    'fiscal_year' => $year,

                    'standard_memf' => $standardMemf,
                    'standard_dbf' => $standardDbf,
                    'standard_laf' => $standardLaf,
                    'standard_wfsa' => $standardWfsa,
                    'standard_annual_dues' => $standardDues,

                    'memf_paid' => $memfPaid,
                    'dbf_paid' => $dbfPaid,
                    'laf_paid' => $lafPaid,
                    'wfsa_paid' => $wfsaPaid,
                    'total_paid' => $totalPaid,

                    'preliminary_balance' => $balance,
                    'preliminary_payment_status' => $status,

                    'payment_item_count' => (int) (
                        $payment->payment_item_count ?? 0
                    ),
                ];
            });

        $this->cmePayments = DB::table('cme_program_registration as c')
            ->join('payments as p', function ($join) {
                $join->on(
                    'p.payment_ref_no',
                    '=',
                    'c.payment_ref_no'
                )->on(
                    'p.member_id_no',
                    '=',
                    'c.member_id_no'
                );
            })
            ->where('c.member_id_no', $memberId)
            ->select([
                'c.cme_year',
                'c.cme_program_code',
                'c.cme_reg_date',
                'c.payment_ref_no',
                'p.payment_date',
                'p.payment_total_amt',
                'p.payment_discounted_amt',
                'p.payment_type',
                'p.or_no',
                'p.stat as payment_status',
            ])
            ->orderByDesc('p.payment_date')
            ->get();

        $currentFiscalYear = (int) date('Y');

        $this->advancePayments = DB::table('payment_items as pi')
            ->join('payments as p', function ($join) {
                $join->on('p.payment_ref_no', '=', 'pi.payment_ref_no')
                    ->on('p.member_id_no', '=', 'pi.member_id_no');
            })
            ->where('pi.member_id_no', $memberId)
            ->where('pi.tran_code', 'MEMF')
            ->where('pi.stat', 1)
            ->where('p.stat', 1)
            ->where('pi.fiscal_year', '>', $currentFiscalYear)
            ->where(function ($query) {
                $query->where('pi.item_code', 'like', 'RM%')
                    ->orWhere('pi.item_code', 'like', 'TM%')
                    ->orWhere('pi.item_code', 'like', 'LM%');
            })
            ->selectRaw('
                pi.fiscal_year,
                SUM(pi.amount_due) AS total_credit
            ')
            ->groupBy('pi.fiscal_year')
            ->orderBy('pi.fiscal_year')
            ->get();

        $this->financialRecordsLoaded = true;
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('certificateOfGoodStanding')
                ->label('Certificate of Good Standing')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url(fn () =>
                    \App\Filament\Pages\CertificateOfGoodStanding::getUrl([
                        'member' => $this->record->member_id_no,
                    ])
                ),

            ActionGroup::make([

                EditAction::make()
                    ->color('gray')
                    ->visible(
                        fn () => auth()->user()?->can('members_edit')
                    ),

                Action::make('updatePhoto')
                    ->label('Update Photo')
                    ->icon('heroicon-o-camera')
                    ->color('gray')
                    ->visible(
                        fn () => auth()->user()?->can('members_edit')
                    )
                    ->form([

                        FormFileUpload::make('photo')
                            ->image()
                            ->required()
                            ->disk('local')
                            ->directory('temp/member-photos'),

                    ])
                    ->action(function (array $data): void {

                        $path = storage_path(
                            'app/private/' . $data['photo']
                        );

                        $binary = file_get_contents($path);

                        $hex = bin2hex($binary);

                        DB::statement(
                            "
                            UPDATE member
                            SET mem_pic = CONVERT(varbinary(max), ?, 2)
                            WHERE member_id_no = ?
                            ",
                            [
                                $hex,
                                $this->record->member_id_no,
                            ]
                        );

                        Storage::disk('local')->delete(
                            $data['photo']
                        );

                        $this->record->refresh();

                        Notification::make()
                            ->title('Photo updated successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('payDues')
                    ->label('New Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->visible(fn () => auth()->user()?->can('payment_create'))
                    ->modalHeading(fn () => 'New Payment: ' . $this->record->mem_last_name . ', ' . $this->record->mem_first_name)
                    ->modalWidth('6xl')
                    ->modalContent(fn () => view('filament.payments.new-payment-modal', [
                        'memberId' => $this->record->member_id_no,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->closeModalByClickingAway(false),

                Action::make('soa')
                    ->label('Statement of Account')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->visible(
                        fn () => auth()->user()?->can('members_edit')
                    )
                    ->url(
                        fn () => route('members.statement-of-account', [
                            'member' => $this->record->member_id_no,
                        ])
                    )
                    ->openUrlInNewTab(),

            ])
                ->label('Actions')
                ->color('gray')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button(),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->member_id_no
            . ' - '
            . $this->record->mem_last_name
            . ', '
            . $this->record->mem_first_name
            . ' '
            . $this->record->mem_middle_name;
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/member-search') => 'PSA Member',

            '#' => sprintf(
                '%s - %s',
                $this->record->member_id_no,
                strtoupper(
                    trim(
                        $this->record->mem_first_name . ' ' .
                        $this->record->mem_middle_name . ' ' .
                        $this->record->mem_last_name
                    )
                )
            ),
        ];
    }

    public static function getNavigationItem(): ?string
    {
        return \App\Filament\Pages\MemberSearch::class;
    }
}
