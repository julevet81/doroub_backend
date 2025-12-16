<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'budget',
        'start_date',
        'end_date',
        'status',
        'location',
        'description',
    ];

    public function assistances()
    {
        return $this->belongsToMany(AssistanceItem::class, 'project_assistances')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function volunteers()
    {
        return $this->belongsToMany(Volunteer::class, 'project_volunteers')
            ->withPivot('position')
            ->withTimestamps();
    }
    public function projectAssistances()
    {
        return $this->hasMany(ProjectAssistance::class);
    }

    public function projectVolunteers()
    {
        return $this->hasMany(ProjectVolunteer::class);
    }

    public function transaction()
    {
        return $this->hasOne(InventoryTransaction::class);
    }
}
