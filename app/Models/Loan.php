<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'device_id',
        'beneficiary_id',
        'new_beneficiary',
        'loan_date',
        'notes',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
    
}
