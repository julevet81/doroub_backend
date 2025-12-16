<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'birth_place',
        'job',
        'study_level',
        'health_status',
        'insured',
        'income_source',
    ];

    public function beneficiary()
    {
        return $this->hasMany(Beneficiary::class, 'partner_id');
    }
}
