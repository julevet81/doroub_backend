<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'amount',
        'transaction_type',
        'donor_id',
        'beneficiary_id',
        'attachment',
        'project_id',
        'orientation',
        'out_orientation',
        'payment_method',
        'previous_balance',
        'new_balance',
        'description',
        'transaction_date',

    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
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
