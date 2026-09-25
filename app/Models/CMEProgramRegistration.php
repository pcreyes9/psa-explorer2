<?php

namespace App\Models;

use App\Models\CMEProgram;
use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class CMEProgramRegistration extends Model
{
    protected $table = 'cme_program_registration';

    public $timestamps = false;

    protected $fillable = [
        'member_id_no',
        'cme_program_code',
        'payment_ref_no',
    ];

    public function cmeProgram()
    {
        return $this->belongsTo(
            CMEProgram::class,
            'cme_program_code',
            'cme_program_code'
        );
    }

    public function member()
    {
        return $this->belongsTo(
            Member::class,
            'member_id_no',
            'member_id_no'
        );
    }
}