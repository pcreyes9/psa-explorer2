<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionTypeItem extends Model
{
    protected $table = 'transaction_type_item';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'item_amount' => 'decimal:2',
            'stat' => 'boolean',
        ];
    }
}
