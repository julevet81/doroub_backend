<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'budget',
        'remaining_amount',
        'start_date',
        'end_date',
        'status',
        'location',
        'description',
    ];

    public function items()
    {
        return $this->belongsToMany(AssistanceItem::class, 'project_assistances')
            ->withPivot('quantity', 'rest_in_project')
            ->withTimestamps();
    }

    public function volunteers()
    {
        return $this->belongsToMany(Volunteer::class, 'project_volunteers')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function transaction()
    {
        return $this->hasOne(InventoryTransaction::class);
    }
}
