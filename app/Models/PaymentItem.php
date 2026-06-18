<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $table = 'payment_items';

    public $timestamps = false;

    protected $guarded = [];

    public function payment()
    {
        return $this->belongsTo(
            Payment::class,
            'payment_ref_no',
            'payment_ref_no'
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