<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CMEProgramRate extends Model
{
    protected $table = 'cme_program_rate';

    public $timestamps = false;

    protected $fillable = [
        'cme_program_code',
        'charge_code',
        'fullrate',
        'dailyrate',
        'prerate',
    ];

    protected function casts(): array
    {
        return [
            'fullrate'  => 'decimal:2',
            'dailyrate' => 'decimal:2',
            'prerate'   => 'decimal:2',
        ];
    }
}