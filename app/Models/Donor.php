<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donor extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'full_name',
        'activity',
        'phone',
        'assistance_category_id',
        'description',
    ];

    public function assistanceCategory()
    {
        return $this->belongsTo(AssistanceCategory::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
