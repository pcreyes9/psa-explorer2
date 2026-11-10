<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_ref_no';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payment_total_amt' => 'decimal:2',
            'payment_gross_amt' => 'decimal:2',
            'payment_discounted_amt' => 'decimal:2',
            'payment_tax_amt' => 'decimal:2',
            'payment_date' => 'date',
            'credit_card_exp' => 'date',
            'stat' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class,
            'member_id_no',
            'member_id_no'
        );
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(
            MemberLedger::class,
            'payment_ref_no',
            'payment_ref_no'
        );
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(
            PaymentItem::class,
            'payment_ref_no',
            'payment_ref_no'
        );
    }
}
