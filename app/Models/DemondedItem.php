<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemondedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'demond_id',
        'assistance_item_id',
        'quantity',
    ];

    public function demond()
    {
        return $this->belongsTo(Demond::class);
    }

    public function assistanceItem()
    {
        return $this->belongsTo(AssistanceItem::class);     
    }
}
