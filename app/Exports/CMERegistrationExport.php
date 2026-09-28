<?php

namespace App\Exports;

use App\Models\CMEProgram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CMERegistrationExport implements WithMultipleSheets
{
    public function __construct(
        protected CMEProgram $program
    ) {
    }

    public function sheets(): array
    {
        return [
            new CMERegistrationSheet($this->program),
            new CMERegistrationSummarySheet($this->program),
        ];
    }
}
