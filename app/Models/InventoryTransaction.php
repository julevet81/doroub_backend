<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_type',
        'donor_id',
        'orientation_out',
        'project_id',
        'beneficiary_id',
        'transaction_date',
        'notes',
    ];
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function assistanceItems()
    {
        return $this->belongsToMany(AssistanceItem::class, 'transaction_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
