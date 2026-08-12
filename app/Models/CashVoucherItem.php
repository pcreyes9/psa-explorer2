<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashVoucherItem extends Model
{
    protected $fillable = [
        'cash_voucher_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // public function cashVoucher()
    // {
    //     return $this->belongsTo(CashVoucher::class);
    // }
    public function voucher()
    {
        return $this->belongsTo(
            CashVoucher::class,
            'cash_voucher_id'
        );
    }
}