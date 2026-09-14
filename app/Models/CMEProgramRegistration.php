<?php

namespace App\Models;

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
}