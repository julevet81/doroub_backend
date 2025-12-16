<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'full_name',
        'date_of_birth',
        'gender',
        'study_level',
        'school',
        'health_status',
        'job',
        'notes',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
