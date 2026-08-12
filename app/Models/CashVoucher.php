<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CashVoucherItem;

class CashVoucher extends Model
{
    protected $fillable = [
        'voucher_no',
        'date',
        'pay_to',
        'address',
        'check_no',
        'total_amount',
        'approved_by',
        'checked_by',
        'received_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(CashVoucherItem::class);
    }
}