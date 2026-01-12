<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demond extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'demand_date',
        'treated_by',
        'status',
        'attachement',
        'description',
    ];


    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function items()
    {
        return $this->belongsToMany(
            AssistanceItem::class,
            'demonded_items'
        )->withPivot('quantity')->withTimestamps();
    }

    public function treatedBy()
    {
        return $this->belongsTo(User::class, 'treated_by');
    }
}
