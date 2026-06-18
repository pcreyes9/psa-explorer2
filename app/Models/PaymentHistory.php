<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = 'payments_histo';

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