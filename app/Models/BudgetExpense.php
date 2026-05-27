<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetExpense extends Model
{
    protected $fillable = ['budget_id', 'label', 'category', 'amount', 'date', 'type', 'notes'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];
    public function budget(): BelongsTo { return $this->belongsTo(Budget::class); }
}
