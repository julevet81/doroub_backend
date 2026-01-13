<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benefice extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'type',
        'amount',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function items()
    {
        return $this->belongsToMany(
            AssistanceItem::class,
            'benefice_items'
        )->withPivot('quantity')->withTimestamps();
    }
}
