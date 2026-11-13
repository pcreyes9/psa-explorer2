<?php

namespace App\Filament\Resources\CMEPrograms\Schemas;

use App\Support\BulkRegisterCmeMembers;
use App\Support\CMERegistrationMemberFileReader;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use App\Models\CMEProgram;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;

class CMEProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('ProgramTabs')
                    ->tabs([

                        Tab::make('Program Information')
                            ->icon('heroicon-m-document-text')
                            ->schema([

                                Section::make('Basic Information')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('cme_program_code')
                                            ->label('Program Code'),

                                        TextEntry::make('cme_year')
                                            ->label('Fiscal Year'),

                                        TextEntry::make('cme_program_type')
                                            ->label('Program Type'),

                                        TextEntry::make('cme_title')
                                            ->label('Program Title')
                                            ->columnSpanFull(),

                                        TextEntry::make('cme_topic')
                                            ->label('Topic')
                                            ->placeholder('-')
                                            ->columnSpanFull(),

                                    ]),

                                Section::make('Schedule & Venue')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('cme_venue')
                                            ->label('Venue')
                                            ->columnSpanFull(),

                                        TextEntry::make('cme_startdate')
                                            ->label('Start Date')
                                            ->date(),

                                        TextEntry::make('cme_enddate')
                                            ->label('End Date')
                                            ->date(),

                                        TextEntry::make('cme_equiv_pts')
                                            ->label('Equivalent Points')
                                            ->placeholder('-'),

                                    ]),

                                Section::make('Administration')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('cme_chair')
                                            ->label('Chairman')
                                            ->placeholder('-'),

                                        TextEntry::make('cme_incumbent_prez')
                                            ->label('Incumbent President')
                                            ->placeholder('-'),

                                        TextEntry::make('cme_prc_ref')
                                            ->label('PRC Reference No.')
                                            ->placeholder('-'),

                                        TextEntry::make('cme_budget')
                                            ->label('Budget')
                                            ->money('PHP')
                                            ->placeholder('-'),

                                        TextEntry::make('stat')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(
                                                fn (bool $state) => $state
                                                    ? 'Active'
                                                    : 'Inactive'
                                            )
                                            ->color(
                                                fn (bool $state) => $state
                                                    ? 'success'
                                                    : 'danger'
                                            ),

                                    ]),

                                Section::make('Description')
                                    ->schema([

                                        TextEntry::make('cme_desc')
                                            ->label('CME Description')
                                            ->placeholder('-'),

                                    ]),

                            ]),

                        Tab::make('Rates')
                            ->icon('heroicon-m-banknotes')
                            ->schema([

                                Section::make('Convention Fees')
                                    ->description(
                                        'Registration rates for this CME program.'
                                    )
                                    ->schema([

                                        View::make('filament.cme-program.rates')
                                            ->viewData(fn ($record) => [
                                                'record' => $record,
                                            ]),

                                    ]),

                            ]),

                        Tab::make('Registrations')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->schema([

                                Section::make('Registrations')
                                    ->description('Members registered for this CME program.')
                                    ->headerActions([

                                        Action::make('addMember')
                                            ->label('Add Member')
                                            ->icon('heroicon-o-user-plus')
                                            ->color('primary')
                                            ->modalHeading('Add Member to Registration')
                                            ->modalDescription(
                                                'Search and select an existing PSA member (RM, TM, or LM).'
                                            )
                                            ->modalWidth('2xl')
                                            ->modalSubmitActionLabel('Register Member')
                                            ->fillForm(function ($record): array {
                                                return [
                                                    'payment_ref_no' => self::generatePaymentReferenceNumber(
                                                        $record->cme_program_code
                                                    ),
                                                ];
                                            })
                                            ->form([

                                                Select::make('member_id_no')
                                                    ->label('Search PSA Member')
                                                    ->placeholder('Enter PSA ID or member name...')
                                                    ->searchable()
                                                    ->required()
                                                    ->columnSpanFull()
                                                    ->getSearchResultsUsing(function (string $search): array {

                                                        $search = trim($search);

                                                        if (strlen($search) < 2) {
                                                            return [];
                                                        }

                                                        return DB::table('member')
                                                            ->select([
                                                                'member_id_no',
                                                                'mem_first_name',
                                                                'mem_middle_name',
                                                                'mem_last_name',
                                                                'psa_mem_type',
                                                            ])
                                                            ->whereRaw(
                                                                "UPPER(LTRIM(RTRIM(psa_mem_type))) IN ('RM', 'TM', 'LM')"
                                                            )
                                                            ->where(function ($query) use ($search) {
                                                                $query->where(
                                                                    'member_id_no',
                                                                    'LIKE',
                                                                    "%{$search}%"
                                                                )
                                                                ->orWhere(
                                                                    'mem_first_name',
                                                                    'LIKE',
                                                                    "%{$search}%"
                                                                )
                                                                ->orWhere(
                                                                    'mem_middle_name',
                                                                    'LIKE',
                                                                    "%{$search}%"
                                                                )
                                                                ->orWhere(
                                                                    'mem_last_name',
                                                                    'LIKE',
                                                                    "%{$search}%"
                                                                );
                                                            })
                                                            ->orderBy('mem_last_name')
                                                            ->orderBy('mem_first_name')
                                                            ->limit(15)
                                                            ->get()
                                                            ->mapWithKeys(function ($member) {

                                                                $fullName = trim(
                                                                    ($member->mem_last_name ?? '') . ', ' .
                                                                    ($member->mem_first_name ?? '') . ' ' .
                                                                    ($member->mem_middle_name ?? '')
                                                                );

                                                                return [
                                                                    $member->member_id_no =>
                                                                        "{$fullName} ({$member->member_id_no}) - {$member->psa_mem_type}",
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->getOptionLabelUsing(function ($value): ?string {

                                                        if (!$value) {
                                                            return null;
                                                        }

                                                        $member = DB::table('member')
                                                            ->select([
                                                                'member_id_no',
                                                                'mem_first_name',
                                                                'mem_middle_name',
                                                                'mem_last_name',
                                                                'psa_mem_type',
                                                            ])
                                                            ->where('member_id_no', $value)
                                                            ->first();

                                                        if (!$member) {
                                                            return null;
                                                        }

                                                        $fullName = trim(
                                                            ($member->mem_last_name ?? '') . ', ' .
                                                            ($member->mem_first_name ?? '') . ' ' .
                                                            ($member->mem_middle_name ?? '')
                                                        );

                                                        return "{$fullName} ({$member->member_id_no}) - {$member->psa_mem_type}";
                                                    }),

                                                TextInput::make('payment_ref_no')
                                                    ->label('Payment Reference Number')
                                                    ->required()
                                                    ->maxLength(20)
                                                    ->columnSpanFull()
                                                    ->helperText(
                                                        'Automatically generated based on the CME program. You may edit this number before registration.'
                                                    ),

                                            ])
                                            ->action(function (array $data, $record): void {

                                                $memberId = $data['member_id_no'];
                                                $paymentRefNo = trim($data['payment_ref_no']);

                                                if ($paymentRefNo === '') {
                                                    throw ValidationException::withMessages([
                                                        'payment_ref_no' => 'Payment reference number is required.',
                                                    ]);
                                                }

                                                DB::transaction(function () use (
                                                    $record,
                                                    $memberId,
                                                    $paymentRefNo
                                                ) {

                                                    DB::select(
                                                        '
                                                        SELECT cme_program_code
                                                        FROM dbo.cme_program WITH (UPDLOCK, HOLDLOCK)
                                                        WHERE cme_program_code = ?
                                                        ',
                                                        [$record->cme_program_code]
                                                    );

                                                    $member = DB::table('member')
                                                        ->where('member_id_no', $memberId)
                                                        ->whereRaw(
                                                            "UPPER(LTRIM(RTRIM(psa_mem_type))) IN ('RM', 'TM', 'LM')"
                                                        )
                                                        ->first();

                                                    if (!$member) {
                                                        throw ValidationException::withMessages([
                                                            'member_id_no' => 'Member not found or not eligible for registration.',
                                                        ]);
                                                    }

                                                    $duplicateMember = DB::table('cme_program_registration')
                                                        ->where(
                                                            'cme_program_code',
                                                            $record->cme_program_code
                                                        )
                                                        ->where('member_id_no', $memberId)
                                                        ->exists();

                                                    if ($duplicateMember) {
                                                        throw ValidationException::withMessages([
                                                            'member_id_no' => 'This member is already registered in this CME program.',
                                                        ]);
                                                    }

                                                    $duplicateReference = DB::table('cme_program_registration')
                                                        ->where('payment_ref_no', $paymentRefNo)
                                                        ->exists();

                                                    if ($duplicateReference) {
                                                        throw ValidationException::withMessages([
                                                            'payment_ref_no' => 'This payment reference number already exists. Please enter another number.',
                                                        ]);
                                                    }

                                                    $program = DB::table('cme_program')
                                                        ->where(
                                                            'cme_program_code',
                                                            $record->cme_program_code
                                                        )
                                                        ->first();

                                                    if (!$program) {
                                                        throw ValidationException::withMessages([
                                                            'member_id_no' => 'CME program not found.',
                                                        ]);
                                                    }

                                                    $user = Auth::user();

                                                    $registeredBy = $user?->username
                                                        ?? $user?->name
                                                        ?? (string) Auth::id();

                                                    DB::table('cme_program_registration')->insert([
                                                        'cme_year' => $program->cme_year,
                                                        'cme_program_code' => $program->cme_program_code,
                                                        'member_id_no' => $member->member_id_no,
                                                        'cme_reg_date' => now()->toDateString(),
                                                        'payment_ref_no' => $paymentRefNo,
                                                        'registeredby' => substr($registeredBy, 0, 20),
                                                    ]);
                                                });

                                                Notification::make()
                                                    ->title('Member Successfully Registered')
                                                    ->body("Payment Reference: {$paymentRefNo}")
                                                    ->success()
                                                    ->send();
                                            }),

                                        Action::make('bulkAddMembers')
                                            ->label('Bulk Add Members')
                                            ->icon('heroicon-o-user-group')
                                            ->color('info')
                                            ->modalHeading('Bulk Add CME Members')
                                            ->modalDescription(
                                                'Select multiple members or upload an Excel/CSV file. Review the registrations before confirming.'
                                            )
                                            ->modalWidth('4xl')
                                            ->modalSubmitActionLabel('Confirm Registration')
                                            ->steps([

                                                Step::make('Select Members')
                                                    ->description(
                                                        'Search for members or upload a file with PSA ID and Payment Reference Number columns.'
                                                    )
                                                    ->icon('heroicon-o-users')
                                                    ->schema([

                                                        Select::make('member_ids')
                                                            ->label('Select Members')
                                                            ->multiple()
                                                            ->searchable()
                                                            ->placeholder('Search by PSA ID or member name...')
                                                            ->helperText(
                                                                'Only RM, TM, and LM members are eligible.'
                                                            )
                                                            ->getSearchResultsUsing(function (string $search): array {

                                                                $search = trim($search);

                                                                if (strlen($search) < 2) {
                                                                    return [];
                                                                }

                                                                return DB::table('member')
                                                                    ->select([
                                                                        'member_id_no',
                                                                        'mem_first_name',
                                                                        'mem_middle_name',
                                                                        'mem_last_name',
                                                                        'psa_mem_type',
                                                                    ])
                                                                    ->whereRaw(
                                                                        "UPPER(LTRIM(RTRIM(psa_mem_type))) IN ('RM', 'TM', 'LM')"
                                                                    )
                                                                    ->where(function ($query) use ($search) {
                                                                        $query->where(
                                                                            'member_id_no',
                                                                            'LIKE',
                                                                            "%{$search}%"
                                                                        )
                                                                        ->orWhere(
                                                                            'mem_first_name',
                                                                            'LIKE',
                                                                            "%{$search}%"
                                                                        )
                                                                        ->orWhere(
                                                                            'mem_middle_name',
                                                                            'LIKE',
                                                                            "%{$search}%"
                                                                        )
                                                                        ->orWhere(
                                                                            'mem_last_name',
                                                                            'LIKE',
                                                                            "%{$search}%"
                                                                        );
                                                                    })
                                                                    ->orderBy('mem_last_name')
                                                                    ->orderBy('mem_first_name')
                                                                    ->limit(50)
                                                                    ->get()
                                                                    ->mapWithKeys(function ($member) {

                                                                        $fullName = trim(
                                                                            ($member->mem_last_name ?? '') . ', ' .
                                                                            ($member->mem_first_name ?? '') . ' ' .
                                                                            ($member->mem_middle_name ?? '')
                                                                        );

                                                                        return [
                                                                            $member->member_id_no =>
                                                                                "{$fullName} ({$member->member_id_no}) - {$member->psa_mem_type}",
                                                                        ];
                                                                    })
                                                                    ->all();
                                                            })
                                                            ->getOptionLabelsUsing(function (array $values): array {

                                                                if (empty($values)) {
                                                                    return [];
                                                                }

                                                                return DB::table('member')
                                                                    ->select([
                                                                        'member_id_no',
                                                                        'mem_first_name',
                                                                        'mem_middle_name',
                                                                        'mem_last_name',
                                                                        'psa_mem_type',
                                                                    ])
                                                                    ->whereIn('member_id_no', $values)
                                                                    ->get()
                                                                    ->mapWithKeys(function ($member) {

                                                                        $fullName = trim(
                                                                            ($member->mem_last_name ?? '') . ', ' .
                                                                            ($member->mem_first_name ?? '') . ' ' .
                                                                            ($member->mem_middle_name ?? '')
                                                                        );

                                                                        return [
                                                                            $member->member_id_no =>
                                                                                "{$fullName} ({$member->member_id_no}) - {$member->psa_mem_type}",
                                                                        ];
                                                                    })
                                                                    ->all();
                                                            }),

                                                        FileUpload::make('member_file')
                                                            ->label('Or Upload Excel / CSV')
                                                            ->storeFiles(false)
                                                            ->acceptedFileTypes([
                                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                                'application/vnd.ms-excel',
                                                                'text/csv',
                                                                'text/plain',
                                                            ])
                                                            ->maxSize(10240)
                                                            ->helperText(
                                                                'For uploads, include PSA ID and Payment Reference Number columns. Each reference must be unique and no longer than 20 characters. Use either manual selection or file upload, not both.'
                                                            ),

                                                    ])
                                                    ->columns(1),

                                                Step::make('Review Registrations')
                                                    ->description(
                                                        'Review eligible members and skipped registrations before confirming.'
                                                    )
                                                    ->icon('heroicon-o-clipboard-document-check')
                                                    ->schema([

                                                        Placeholder::make('registration_preview')
                                                            ->label('Registration Preview')
                                                            ->content(function (Get $get, CMEProgram $record) {

                                                                $selectedMemberIds = $get('member_ids') ?? [];
                                                                $uploadedFile = $get('member_file');

                                                                if (
                                                                    !empty($selectedMemberIds)
                                                                    && filled($uploadedFile)
                                                                ) {
                                                                    return 'Please use either manual selection or file upload, not both. Go back and select only one method.';
                                                                }

                                                                if (
                                                                    empty($selectedMemberIds)
                                                                    && blank($uploadedFile)
                                                                ) {
                                                                    return 'No members selected yet. Go back and select members or upload an Excel/CSV file.';
                                                                }

                                                                $memberIds = $selectedMemberIds;
                                                                $paymentReferences = [];

                                                                if (filled($uploadedFile)) {

                                                                    if (!method_exists($uploadedFile, 'getRealPath')) {
                                                                        return 'The uploaded file is not available. Please upload it again.';
                                                                    }

                                                                    $filePath = $uploadedFile->getRealPath();

                                                                    if (!$filePath || !file_exists($filePath)) {
                                                                        return 'The temporary uploaded file could not be found. Please upload it again.';
                                                                    }

                                                                    $fileData = app(
                                                                        CMERegistrationMemberFileReader::class
                                                                    )->read($filePath);

                                                                    $memberIds = $fileData['member_ids'];
                                                                    $paymentReferences = $fileData['payment_references'];
                                                                }

                                                                $preview = app(
                                                                    BulkRegisterCmeMembers::class
                                                                )->preview(
                                                                    $record,
                                                                    $memberIds,
                                                                    $paymentReferences
                                                                );

                                                                return view(
                                                                    'filament.cme-program.bulk-registration-preview',
                                                                    [
                                                                        'preview' => $preview,
                                                                    ]
                                                                );
                                                            })
                                                            ->columnSpanFull(),

                                                    ])
                                                    ->columns(1),

                                            ])

                                            ->action(function (array $data, CMEProgram $record): void {

                                                $selectedMemberIds = $data['member_ids'] ?? [];
                                                $uploadedFile = $data['member_file'] ?? null;

                                                if (
                                                    !empty($selectedMemberIds)
                                                    && filled($uploadedFile)
                                                ) {
                                                    throw ValidationException::withMessages([
                                                        'member_file' => 'Please use either manual selection or file upload, not both.',
                                                    ]);
                                                }

                                                if (
                                                    empty($selectedMemberIds)
                                                    && blank($uploadedFile)
                                                ) {
                                                    throw ValidationException::withMessages([
                                                        'member_ids' => 'Please select members or upload a file.',
                                                    ]);
                                                }

                                                $memberIds = $selectedMemberIds;
                                                $paymentReferences = [];

                                                if (filled($uploadedFile)) {

                                                    if (!method_exists($uploadedFile, 'getRealPath')) {
                                                        throw ValidationException::withMessages([
                                                            'member_file' => 'The uploaded file is not available. Please upload it again.',
                                                        ]);
                                                    }

                                                    $filePath = $uploadedFile->getRealPath();

                                                    if (!$filePath || !file_exists($filePath)) {
                                                        throw ValidationException::withMessages([
                                                            'member_file' => 'The temporary uploaded file could not be found. Please upload it again.',
                                                        ]);
                                                    }

                                                    $fileData = app(
                                                        CMERegistrationMemberFileReader::class
                                                    )->read($filePath);

                                                    $memberIds = $fileData['member_ids'];
                                                    $paymentReferences = $fileData['payment_references'];
                                                }

                                                $result = app(
                                                    BulkRegisterCmeMembers::class
                                                )->execute(
                                                    $record,
                                                    $memberIds,
                                                    $paymentReferences
                                                );

                                                Notification::make()
                                                    ->title('Bulk Registration Completed')
                                                    ->body(
                                                        "Successfully registered: {$result['processed']}\n" .
                                                        "Already registered / duplicates skipped: {$result['skipped']}\n" .
                                                        "Invalid or ineligible PSA IDs: {$result['invalid']}"
                                                    )
                                                    ->success()
                                                    ->persistent()
                                                    ->send();
                                            }),

                                        Action::make('removeDelegate')
                                            ->label('Remove Delegate')
                                            ->icon('heroicon-o-user-minus')
                                            ->color('danger')
                                            ->visible(fn (CMEProgram $record): bool => DB::table('cme_program_registrationNM')
                                                ->where('cme_program_code', $record->cme_program_code)
                                                ->exists()
                                                || DB::table('cme_program_registration')
                                                    ->where('cme_program_code', $record->cme_program_code)
                                                    ->exists())
                                            ->modalHeading('Remove Delegate from CME Program')
                                            ->modalDescription(
                                                'This removes the registration from this CME program. It does not delete any separate payment record.'
                                            )
                                            ->modalSubmitActionLabel('Remove Delegate')
                                            ->form([
                                                Select::make('registration_key')
                                                    ->label('Select Delegate')
                                                    ->required()
                                                    ->searchable()
                                                    ->options(function (CMEProgram $record): array {
                                                        $nonMembers = DB::table('cme_program_registrationNM')
                                                            ->where('cme_program_code', $record->cme_program_code)
                                                            ->select([
                                                                'nm_id_no',
                                                                'nm_last_name',
                                                                'nm_first_name',
                                                                'nm_middle_name',
                                                                'payment_ref_no',
                                                            ])
                                                            ->orderBy('nm_last_name')
                                                            ->orderBy('nm_first_name')
                                                            ->get()
                                                            ->mapWithKeys(function ($delegate): array {
                                                                $id = trim((string) $delegate->nm_id_no);
                                                                $name = trim(
                                                                    ($delegate->nm_last_name ?? '') . ', ' .
                                                                    ($delegate->nm_first_name ?? '') . ' ' .
                                                                    ($delegate->nm_middle_name ?? '')
                                                                );
                                                                $reference = trim((string) ($delegate->payment_ref_no ?? ''));

                                                                return [
                                                                    "NM|{$id}" => trim(
                                                                        "Non-member: {$name} ({$id})" .
                                                                        ($reference !== '' ? " — {$reference}" : '')
                                                                    ),
                                                                ];
                                                            });

                                                        $members = DB::table('cme_program_registration as r')
                                                            ->join(
                                                                'member as m',
                                                                'm.member_id_no',
                                                                '=',
                                                                'r.member_id_no'
                                                            )
                                                            ->where('r.cme_program_code', $record->cme_program_code)
                                                            ->select([
                                                                'r.member_id_no',
                                                                'r.payment_ref_no',
                                                                'm.mem_last_name',
                                                                'm.mem_first_name',
                                                                'm.mem_middle_name',
                                                                'm.psa_mem_type',
                                                            ])
                                                            ->orderBy('m.mem_last_name')
                                                            ->orderBy('m.mem_first_name')
                                                            ->get()
                                                            ->mapWithKeys(function ($delegate): array {
                                                                $id = trim((string) $delegate->member_id_no);
                                                                $name = trim(
                                                                    ($delegate->mem_last_name ?? '') . ', ' .
                                                                    ($delegate->mem_first_name ?? '') . ' ' .
                                                                    ($delegate->mem_middle_name ?? '')
                                                                );
                                                                $reference = trim((string) ($delegate->payment_ref_no ?? ''));
                                                                $type = strtoupper(trim((string) $delegate->psa_mem_type));

                                                                return [
                                                                    "MEMBER|{$id}" => trim(
                                                                        "{$type}: {$name} ({$id})" .
                                                                        ($reference !== '' ? " — {$reference}" : '')
                                                                    ),
                                                                ];
                                                            });

                                                        return $nonMembers
                                                            ->merge($members)
                                                            ->all();
                                                    }),
                                            ])
                                            ->action(function (array $data, CMEProgram $record): void {
                                                [$type, $id] = array_pad(
                                                    explode('|', (string) ($data['registration_key'] ?? ''), 2),
                                                    2,
                                                    ''
                                                );

                                                [$table, $idColumn] = match ($type) {
                                                    'NM' => ['cme_program_registrationNM', 'nm_id_no'],
                                                    'MEMBER' => ['cme_program_registration', 'member_id_no'],
                                                    default => throw ValidationException::withMessages([
                                                        'registration_key' => 'Select a valid delegate registration.',
                                                    ]),
                                                };

                                                if ($id === '') {
                                                    throw ValidationException::withMessages([
                                                        'registration_key' => 'Select a valid delegate registration.',
                                                    ]);
                                                }

                                                DB::transaction(function () use (
                                                    $table,
                                                    $idColumn,
                                                    $id,
                                                    $record
                                                ): void {
                                                    $query = DB::table($table)
                                                        ->where('cme_program_code', $record->cme_program_code)
                                                        ->where($idColumn, $id);

                                                    $matchingRows = (clone $query)
                                                        ->lockForUpdate()
                                                        ->count();

                                                    if ($matchingRows !== 1) {
                                                        throw ValidationException::withMessages([
                                                            'registration_key' => $matchingRows === 0
                                                                ? 'This delegate is no longer registered in this CME program.'
                                                                : 'Multiple registrations match this delegate. No registrations were removed.',
                                                        ]);
                                                    }

                                                    $query->delete();
                                                });

                                                Notification::make()
                                                    ->title('Delegate Removed')
                                                    ->body('The delegate registration was removed from this CME program.')
                                                    ->success()
                                                    ->send();
                                            }),

                                        Action::make('export')
                                            ->label('Export Excel')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->color('success')
                                            ->url(fn ($record) => route(
                                                'cme-program.registrations.export',
                                                $record->cme_program_code
                                            )),

                                    ])

                                    ->schema([

                                        View::make('filament.cme-program.registrations')
                                            ->viewData(fn ($record) => [
                                                'record' => $record,
                                            ]),

                                    ]),

                            ]),

                        Tab::make('Attendance')
                            ->icon('heroicon-m-check-badge')
                            ->schema([

                                Section::make('Attendance')
                                    ->description('Coming soon.')
                                    ->schema([]),

                            ]),

                    ])
                    ->columnSpanFull(),

            ]);
    }

    private static function generatePaymentReferenceNumber(
        string $programCode
    ): string {

        $program = DB::table('cme_program')
            ->select([
                'cme_program_code',
                'cme_year',
                'cme_program_type',
            ])
            ->where('cme_program_code', $programCode)
            ->first();

        if (!$program) {
            throw ValidationException::withMessages([
                'payment_ref_no' => 'CME program not found.',
            ]);
        }

        $programType = strtolower(
            trim($program->cme_program_type)
        );

        $typeCode = match ($programType) {
            'mid-year' => '001',
            'annual convention' => '002',
            default => null,
        };

        if (!$typeCode) {
            throw ValidationException::withMessages([
                'payment_ref_no' => 'Invalid CME program type.',
            ]);
        }

        $year = substr(
            (string) $program->cme_year,
            -2
        );

        $prefix = 'V' . $year . $typeCode;

        $lastReference = DB::table('cme_program_registration')
            ->where(
                'payment_ref_no',
                'LIKE',
                $prefix . '%'
            )
            ->orderByDesc('payment_ref_no')
            ->value('payment_ref_no');

        $lastSequence = $lastReference
            ? (int) substr(
                $lastReference,
                strlen($prefix)
            )
            : 0;

        $nextSequence = $lastSequence + 1;

        if ($nextSequence > 9999) {
            throw ValidationException::withMessages([
                'payment_ref_no' =>
                    'The payment reference number sequence has reached its limit.',
            ]);
        }

        return $prefix . str_pad(
            (string) $nextSequence,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
