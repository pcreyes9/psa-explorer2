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

    /*
    |--------------------------------------------------------------------------
    | Financial Records Properties
    |--------------------------------------------------------------------------
    */

    public bool $financialRecordsLoaded = false;

    public $financialPayments = [];

    public $financialArchives = [];

    public $financialBalances = [];

    public $cmePayments = [];

    /*
    |--------------------------------------------------------------------------
    | Load Financial Records
    |--------------------------------------------------------------------------
    |
    | Financial records are loaded only when requested.
    |
    */

    public function loadFinancialRecords(): void
    {
        $memberId = $this->record->member_id_no;

        /*
        |--------------------------------------------------------------------------
        | Membership Payments
        |--------------------------------------------------------------------------
        */

        $this->financialPayments = $this->record
            ->payments()
            ->with('paymentItems.transactionTypeItem')
            ->orderByDesc('payment_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Archived Payments
        |--------------------------------------------------------------------------
        */

        $this->financialArchives = $this->record
            ->archivedPayments()
            ->orderByDesc('payment_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ledger Balances
        |--------------------------------------------------------------------------
        |
        | Query directly because member_ledger_bal does not have a normal
        | Eloquent primary key.
        |
        */

        $this->financialBalances = MemberLedgerBalance::query()
            ->where('member_id_no', $memberId)
            ->orderByDesc('fiscal_year')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CME Payments
        |--------------------------------------------------------------------------
        |
        | Match CME registrations to payments using both:
        | - member_id_no
        | - payment_ref_no
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Mark Financial Records as Loaded
        |--------------------------------------------------------------------------
        */

        $this->financialRecordsLoaded = true;
        
    }

    /*
    |--------------------------------------------------------------------------
    | Header Actions
    |--------------------------------------------------------------------------
    */

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
                    ->label('Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->visible(
                        fn () => auth()->user()?->can('members_edit')
                    )
                    ->url(
                        fn () => url(
                            '/admin/new-payment?member=' .
                            $this->record->member_id_no
                        )
                    ),

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

    /*
    |--------------------------------------------------------------------------
    | Page Title
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public static function getNavigationItem(): ?string
    {
        return \App\Filament\Pages\MemberSearch::class;
    }
}