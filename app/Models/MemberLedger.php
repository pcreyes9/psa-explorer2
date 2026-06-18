<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLedger extends Model
{
    protected $table = 'member_ledger';

    public $timestamps = false;

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(
            Member::class,
            'member_id_no',
            'member_id_no'
        );
    }

    public function payment()
    {
        return $this->belongsTo(
            Payment::class,
            'payment_ref_no',
            'payment_ref_no'
        );
    }
}