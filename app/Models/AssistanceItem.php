<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity_in_stock',
        'code',
    ];

    public function assistanceCategory()
    {
        return $this->belongsTo(AssistanceCategory::class);
    }

    public function inventoryTransactions()
    {
        return $this->belongsToMany(InventoryTransaction::class, 'transaction_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_assistances')
            ->withPivot('quantity');
    }
}
