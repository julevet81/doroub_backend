<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Volunteer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'membership_id',
        'gender',
        'email',
        'phone_1',
        'phone_2',
        'address',
        'date_of_birth',
        'national_id',
        'joining_date',
        'subscriptions',
        'skills',
        'study_level',
        'grade',
        'section',
        'notes',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_volunteers')
            ->withPivot('position');
    }
    
}
