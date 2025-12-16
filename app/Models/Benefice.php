<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benefice extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'type',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
