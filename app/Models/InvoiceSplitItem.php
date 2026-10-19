<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSplitItem extends Model
{
    protected $table = 'invoice_split_items';
    protected $guarded = [];

    protected $casts = [
        'beneficiary_amount' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
