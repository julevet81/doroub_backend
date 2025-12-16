<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'serial_number',
        'usage_count',
        'status',
        'is_destructed',
        'destruction_report',
        'destruction_reason',
        'destruction_date', 
        'barcode',
        'is_new',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
