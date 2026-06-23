<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionTypeItem extends Model
{
    protected $table = 'transaction_type_item';

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = null;

    public $incrementing = false;
}