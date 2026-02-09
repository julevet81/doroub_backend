<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerSubscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'volunteer_id',
        'amount',
        'subscription_date',
    ];

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class);
    }
}
