<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_ref_no';

    public $incrementing = false;

    protected $keyType = 'string';

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