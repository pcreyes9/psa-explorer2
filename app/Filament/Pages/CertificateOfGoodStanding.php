<?php

namespace App\Filament\Pages;

use App\Models\Member;
use Filament\Pages\Page;

class CertificateOfGoodStanding extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.certificate-of-good-standing';

    public ?Member $member = null;

    public string $purpose = '';

    public ?string $customPurpose = null;

    public function mount(): void
    {
        $memberId = request()->query('member');

        $this->member = Member::where(
            'member_id_no',
            $memberId
        )->firstOrFail();
    }

    public function generate(): void
    {
        $this->validate([
            'purpose' => ['required'],

            'customPurpose' => [
                'nullable',
                'required_if:purpose,custom',
                'string',
                'max:500',
            ],
        ]);

        $finalPurpose = $this->purpose === 'custom'
            ? $this->customPurpose
            : $this->purpose;

        $url = route('certificate.cogs', [
            'member' => $this->member,
            'purpose' => $finalPurpose,
        ]);

        $this->redirect($url, navigate: false);
    }
}