<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        
        'first_name',
        'last_name',
        'date_of_birth',
        'birth_place',
        'phone_1',
        'phone_2',
        'job',
        'health_status',
        'insured',
        'social_status',
        'nbr_in_family',
        'nbr_studing',
        'house_status',
        'district_id',
        'municipality_id',
        'city',
        'neighborhood',
        'first_name_of_wife',
        'last_name_of_wife',
        'date_of_birth_of_wife',
        'birth_place_of_wife',
        'job_of_wife',
        'health_status_of_wife',
        'is_wife_insured',
        'status',
        'notes',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

}
