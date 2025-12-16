<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'inventory_transaction_id',
        'assistance_item_id',
        'quantity',
    ];

    public function inventoryTransaction()
    {
        return $this->belongsTo(InventoryTransaction::class);
    }

    public function assistanceItem()
    {
        return $this->belongsTo(AssistanceItem::class);
    }
}