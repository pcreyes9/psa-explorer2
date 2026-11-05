<?php

namespace App\Livewire\Cme;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class AddCmeMember extends Component
{
    public string $programCode;

    public bool $showModal = false;

    public string $search = '';

    public ?string $selectedMemberId = null;

    public ?array $selectedMemberData = null;

    public string $paymentRefNo = '';

    public function mount(string $programCode): void
    {
        $this->programCode = $programCode;
    }

    /*
    |--------------------------------------------------------------------------
    | Open / Close Modal
    |--------------------------------------------------------------------------
    */

    public function openModal(): void
    {
        $this->resetValidation();

        $this->search = '';
        $this->selectedMemberId = null;
        $this->selectedMemberData = null;

        $this->paymentRefNo = $this->generateReferenceNumber();

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;

        $this->search = '';
        $this->selectedMemberId = null;
        $this->selectedMemberData = null;
        $this->paymentRefNo = '';

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Search Members
    |--------------------------------------------------------------------------
    */

    public function searchMembers(): void
    {
        $this->search = trim($this->search);

        $this->resetValidation();
    }

    public function getMembersProperty()
    {
        if (strlen(trim($this->search)) < 2) {
            return collect();
        }

        $search = trim($this->search);

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
                $query->where('member_id_no', 'LIKE', "%{$search}%")
                    ->orWhere('mem_first_name', 'LIKE', "%{$search}%")
                    ->orWhere('mem_middle_name', 'LIKE', "%{$search}%")
                    ->orWhere('mem_last_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('mem_last_name')
            ->orderBy('mem_first_name')
            ->limit(15)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Select Member
    |--------------------------------------------------------------------------
    */

    public function selectMember(string $memberId): void
    {
        $this->resetValidation();

        // Query 1: Retrieve the member and verify eligibility.
        $member = DB::table('member')
            ->select([
                'member_id_no',
                'mem_first_name',
                'mem_middle_name',
                'mem_last_name',
                'psa_mem_type',
            ])
            ->where('member_id_no', $memberId)
            ->whereRaw(
                "UPPER(LTRIM(RTRIM(psa_mem_type))) IN ('RM', 'TM', 'LM')"
            )
            ->first();

        if (!$member) {
            $this->addError(
                'selectedMemberId',
                'Member not found or not eligible for registration.'
            );

            return;
        }

        // Query 2: Check duplicate registration.
        $alreadyRegistered = DB::table('cme_program_registration')
            ->where('cme_program_code', $this->programCode)
            ->where('member_id_no', $memberId)
            ->exists();

        if ($alreadyRegistered) {
            $this->addError(
                'selectedMemberId',
                'This member is already registered in this CME program.'
            );

            return;
        }

        // Store member details in Livewire instead of querying again.
        $this->selectedMemberId = $memberId;

        $this->selectedMemberData = [
            'member_id_no' => $member->member_id_no,
            'mem_first_name' => $member->mem_first_name,
            'mem_middle_name' => $member->mem_middle_name,
            'mem_last_name' => $member->mem_last_name,
            'psa_mem_type' => $member->psa_mem_type,
        ];

        $this->search = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Payment Reference Number
    |--------------------------------------------------------------------------
    |
    | Format:
    | V + YY + Program Type + 4-digit Sequence
    |
    | Mid-Year          = 001
    | Annual Convention = 002
    |
    | Example:
    | V260020001
    |
    */

    private function generateReferenceNumber(): string
    {
        $program = DB::table('cme_program')
            ->select([
                'cme_program_code',
                'cme_year',
                'cme_program_type',
            ])
            ->where('cme_program_code', $this->programCode)
            ->first();

        if (!$program) {
            throw ValidationException::withMessages([
                'paymentRefNo' => 'CME program not found.',
            ]);
        }

        $programType = strtolower(trim($program->cme_program_type));

        $typeCode = match ($programType) {
            'mid-year' => '001',
            'annual convention' => '002',
            default => null,
        };

        if (!$typeCode) {
            throw ValidationException::withMessages([
                'paymentRefNo' => 'Invalid CME program type.',
            ]);
        }

        $year = substr((string) $program->cme_year, -2);

        $prefix = 'V' . $year . $typeCode;

        $lastReference = DB::table('cme_program_registration')
            ->where('payment_ref_no', 'LIKE', $prefix . '%')
            ->orderByDesc('payment_ref_no')
            ->value('payment_ref_no');

        $lastSequence = $lastReference
            ? (int) substr($lastReference, strlen($prefix))
            : 0;

        $nextSequence = $lastSequence + 1;

        if ($nextSequence > 9999) {
            throw ValidationException::withMessages([
                'paymentRefNo' => 'The reference number sequence has reached its limit.',
            ]);
        }

        return $prefix . str_pad(
            (string) $nextSequence,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save Registration
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $this->validate([
            'selectedMemberId' => [
                'required',
                'string',
                'max:4',
            ],

            'paymentRefNo' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        $paymentRefNo = trim($this->paymentRefNo);

        // Revalidate the selected member before inserting.
        $member = DB::table('member')
            ->where('member_id_no', $this->selectedMemberId)
            ->whereRaw(
                "UPPER(LTRIM(RTRIM(psa_mem_type))) IN ('RM', 'TM', 'LM')"
            )
            ->first();

        if (!$member) {
            throw ValidationException::withMessages([
                'selectedMemberId' => 'The selected member is no longer eligible.',
            ]);
        }

        $program = DB::table('cme_program')
            ->where('cme_program_code', $this->programCode)
            ->first();

        if (!$program) {
            throw ValidationException::withMessages([
                'paymentRefNo' => 'CME program not found.',
            ]);
        }

        DB::transaction(function () use (
            $member,
            $program,
            $paymentRefNo
        ) {
            // Lock the current program record.
            DB::select(
                '
                SELECT cme_program_code
                FROM dbo.cme_program WITH (UPDLOCK, HOLDLOCK)
                WHERE cme_program_code = ?
                ',
                [$this->programCode]
            );

            // Prevent duplicate member registration.
            $duplicateMember = DB::table('cme_program_registration')
                ->where('cme_program_code', $this->programCode)
                ->where('member_id_no', $member->member_id_no)
                ->exists();

            if ($duplicateMember) {
                throw ValidationException::withMessages([
                    'selectedMemberId' =>
                        'This member is already registered in this CME program.',
                ]);
            }

            // Prevent duplicate payment reference.
            $duplicateReference = DB::select(
                '
                SELECT payment_ref_no
                FROM dbo.cme_program_registration WITH (UPDLOCK, HOLDLOCK)
                WHERE payment_ref_no = ?
                ',
                [$paymentRefNo]
            );

            if (count($duplicateReference) > 0) {
                throw ValidationException::withMessages([
                    'paymentRefNo' =>
                        'This payment reference number already exists. Please enter another number.',
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

                'cme_units' => 0,

            ]);
        });

        $this->showModal = false;

        Notification::make()
            ->title('Member Successfully Registered')
            ->body("Payment Reference: {$paymentRefNo}")
            ->success()
            ->send();

        // Refresh the parent Filament page after successful registration.
        $this->redirect(url()->full());
    }

    public function render()
    {
        return view('livewire.cme.add-cme-member');
    }
}