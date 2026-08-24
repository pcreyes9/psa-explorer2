<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Models\MemberLedgerBalance;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\FileUpload;
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
    | Financial Records
    |--------------------------------------------------------------------------
    */

    public bool $financialRecordsLoaded = false;

    public $financialPayments = [];

    public $financialArchives = [];

    public $financialBalances = [];

    /**
     * Load financial records only when requested.
     */
    public function loadFinancialRecords(): void
    {
        $memberId = $this->record->member_id_no;

        /*
        |--------------------------------------------------------------------------
        | Payments
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
        | Do NOT eager-load this relationship.
        |
        | member_ledger_bal does not have a normal Eloquent primary key,
        | so querying it directly prevents Laravel from generating:
        |
        | [member_ledger_bal].[]
        |
        */

        $this->financialBalances = MemberLedgerBalance::query()
            ->where('member_id_no', $memberId)
            ->orderByDesc('fiscal_year')
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
                        fn () =>
                            auth()->user()?->can('members_edit')
                    ),

                Action::make('updatePhoto')
                    ->label('Update Photo')
                    ->icon('heroicon-o-camera')
                    ->color('gray')
                    ->visible(
                        fn () =>
                            auth()->user()?->can('members_edit')
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
                        fn () =>
                            auth()->user()?->can('members_edit')
                    )
                    ->url(
                        fn () =>
                            url(
                                '/admin/new-payment?member=' .
                                $this->record->member_id_no
                            )
                    ),

                Action::make('soa')
                    ->label('Statement of Account')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('gray')
                    ->visible(
                        fn () =>
                            auth()->user()?->can('members_edit')
                    )
                    ->url(
                        fn () =>
                            url(
                                '/admin/new-payment?member=' .
                                $this->record->member_id_no
                            )
                    ),

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