<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingItem extends Model
{
    protected $fillable = [
        'packing_list_id', 'name', 'category', 'quantity', 'unit',
        'unit_price', 'is_checked', 'need_to_buy', 'notes', 'sort_order',
    ];

    protected $casts = [
        'is_checked'   => 'boolean',
        'need_to_buy'  => 'boolean',
        'unit_price'   => 'decimal:2',
        'quantity'     => 'decimal:2',
    ];

    protected $appends = ['subtotal'];

    public function list(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function getSubtotalAttribute(): ?float
    {
        if ($this->unit_price !== null) {
            return (float) ($this->quantity * $this->unit_price);
        }
        return null;
    }
}
