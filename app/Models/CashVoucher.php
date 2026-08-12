<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CashVoucherItem;
use App\Models\User;


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
        'printed_at',
        'printed_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'printed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(CashVoucherItem::class);
    }
    public function printer()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
