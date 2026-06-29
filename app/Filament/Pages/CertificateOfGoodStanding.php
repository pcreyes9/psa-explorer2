<?php

namespace App\Filament\Pages;

use App\Models\Member;
use Filament\Pages\Page;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CertificateOfGoodStandingService;

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
        $this->validate([
            'purpose' => ['required'],
        ]);
        $url = route('certificate.cogs', [
            'member' => $this->member,
            'purpose' => $this->purpose,
        ]);

        $this->js("
            window.open('{$url}', '_blank');
        ");
    }
}
