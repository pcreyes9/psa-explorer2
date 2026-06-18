<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Chapter;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\MemberLedger;
use App\Models\MemberLedgerBalance;

class Member extends Model
{
    protected $table = 'member';

    protected $primaryKey = 'member_id_no';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [
        'mem_pic',
    ];

    public function getPhotoUrlAttribute(): string
    {
        return route('member.photo', $this);
    }

    /*
    |--------------------------------------------------------------------------
    | Lookup Relationships
    |--------------------------------------------------------------------------
    */

    public function chapter()
    {
        return $this->belongsTo(
            Chapter::class,
            'psa_chapter_code',
            'psa_chapter_code'
        );
    }

    public function membershipType()
    {
        return $this->belongsTo(
            MembershipType::class,
            'psa_mem_type',
            'Memtypecode'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Relationships
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'member_id_no',
            'member_id_no'
        );
    }

    public function paymentItems()
    {
        return $this->hasMany(
            PaymentItem::class,
            'member_id_no',
            'member_id_no'
        );
    }

    public function ledgerEntries()
    {
        return $this->hasMany(
            MemberLedger::class,
            'member_id_no',
            'member_id_no'
        );
    }

    public function ledgerBalances()
    {
        return $this->hasMany(
            MemberLedgerBalance::class,
            'member_id_no',
            'member_id_no'
        );
    }
    public function archivedPayments()
    {
        return $this->hasMany(
            PaymentHistory::class,
            'member_id_no',
            'member_id_no'
        );
    }
}