<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $table = 'chapters';

    protected $primaryKey = 'psa_chapter_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function members()
    {
        return $this->hasMany(
            Member::class,
            'psa_chapter_code',
            'psa_chapter_code'
        );
    }
}