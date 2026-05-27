<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingList extends Model
{
    protected $fillable = ['user_id', 'trip_id', 'list_template_id', 'name', 'type', 'icon'];

    protected $appends = ['progress', 'estimated_total', 'to_buy_total'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function trip(): BelongsTo { return $this->belongsTo(Trip::class); }
    public function items(): HasMany { return $this->hasMany(PackingItem::class)->orderBy('category')->orderBy('sort_order'); }

    public function getProgressAttribute(): array
    {
        $total = $this->items()->count();
        $checked = $this->items()->where('is_checked', true)->count();
        return [
            'total'   => $total,
            'checked' => $checked,
            'percent' => $total > 0 ? round(($checked / $total) * 100) : 0,
        ];
    }

    public function getEstimatedTotalAttribute(): float
    {
        return (float) $this->items()
            ->whereNotNull('unit_price')
            ->get()
            ->sum(fn($i) => $i->quantity * $i->unit_price);
    }

    public function getToBuyTotalAttribute(): float
    {
        return (float) $this->items()
            ->where('need_to_buy', true)
            ->whereNotNull('unit_price')
            ->get()
            ->sum(fn($i) => $i->quantity * $i->unit_price);
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\TripMember::class, 'trip_member_id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\TripGroup::class, 'trip_group_id');
    }
}
