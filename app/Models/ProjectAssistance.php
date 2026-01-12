<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAssistance extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'assistance_item_id',
        'quantity',
        'rest_in_project',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assistanceItem()
    {
        return $this->belongsTo(AssistanceItem::class);
    }
}
