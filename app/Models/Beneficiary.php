<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficiary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'phone_1',
        'phone_2',
        'address',
        'social_status',
        'nbr_in_family',
        'partner_id',
        'nbr_studing',
        'job',
        'insured',
        'study_level',
        'health_status',
        'income_source',
        'barcode',
        'district_id',
        'city',
        'neighborhood',
        'house_status',
        'national_id',
        'national_id_at',
        'national_id_from',
        'father_name',
        'mother_name',
        'beneficiary_category_id',
    ];

    public function category()
    {
        return $this->belongsTo(BeneficiaryCategory::class, 'beneficiary_category_id');
    }   

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function partner()
    {
        return $this->belongsTo(PartnerInfo::class, 'partner_id');
    }

    
}