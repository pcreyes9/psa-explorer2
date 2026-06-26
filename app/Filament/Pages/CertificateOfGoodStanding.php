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
        dd($this->purpose);
    }
}
