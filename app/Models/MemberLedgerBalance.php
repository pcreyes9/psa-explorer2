<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLedgerBalance extends Model
{
    protected $table = 'member_ledger_bal';

    protected $primaryKey = null;

    public $incrementing = false;

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
}