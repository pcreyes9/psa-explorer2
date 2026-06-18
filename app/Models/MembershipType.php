<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
    protected $table = 'membership_type';

    protected $primaryKey = 'Memtypecode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function members()
    {
        return $this->hasMany(
            Member::class,
            'psa_mem_type',
            'Memtypecode'
        );
    }
}