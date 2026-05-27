<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetActivity extends Model
{
    protected $fillable = [
        'budget_id', 'name', 'category', 'price_per_person',
        'persons', 'is_planned', 'is_paid', 'date', 'notes',
    ];
    protected $casts = [
        'date'             => 'date',
        'price_per_person' => 'decimal:2',
        'is_planned'       => 'boolean',
        'is_paid'          => 'boolean',
    ];
    protected $appends = ['total'];

    public function budget(): BelongsTo { return $this->belongsTo(Budget::class); }

    public function getTotalAttribute(): float
    {
        return (float) ($this->price_per_person * $this->persons);
    }
}
