<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberHospital extends Model
{
    protected $table = 'member_hospital';

    public $timestamps = false;

    protected $fillable = [
        'member_id_no',
        'hospital',
        'hosp_address',
        'hosp_hours',
        'hosp_tel_no',
        'hosp_designation',
        'hosp_days',
        'hosp_remarks',
        'hosp_primary',
    ];

    protected function casts(): array
    {
        return [
            'hosp_primary' => 'boolean',
        ];
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