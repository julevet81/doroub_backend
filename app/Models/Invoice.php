<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'attachment',
        'financial_transaction_id',

    ];

    public function financialTransaction()
    {
        return $this->belongsTo(FinancialTransaction::class);
    }
}
