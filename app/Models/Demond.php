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
        'description',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
