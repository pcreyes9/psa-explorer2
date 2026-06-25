<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CMEProgram extends Model
{
    protected $table = 'cme_program';

    protected $primaryKey = 'cme_program_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cme_program_code',
        'cme_year',
        'cme_program_type',
        'cme_title',
        'cme_topic',
        'cme_chair',
        'cme_incumbent_prez',
        'cme_desc',
        'cme_startdate',
        'cme_enddate',
        'cme_equiv_pts',
        'cme_prc_ref',
        'cme_venue',
        'cme_budget',
        'stat',
        'createdby',
    ];

    protected function casts(): array
    {
        return [
            'cme_startdate' => 'date',
            'cme_enddate'   => 'date',
            'stat'          => 'boolean',
        ];
    }
}
