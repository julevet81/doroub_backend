<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectVolunteer extends Model
{
    protected $fillable = [
        'project_id',
        'volunteer_id',
        'position',
    ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class);
    }
}
