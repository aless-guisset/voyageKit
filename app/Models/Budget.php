<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = ['trip_id', 'currency', 'total_target'];

    protected $appends = [
        'total_income', 'total_spent', 'total_planned',
        'remaining', 'balance', 'activities_total',
    ];

    public function trip(): BelongsTo { return $this->belongsTo(Trip::class); }
    public function incomes(): HasMany { return $this->hasMany(BudgetIncome::class)->orderBy('date'); }
    public function expenses(): HasMany { return $this->hasMany(BudgetExpense::class)->orderBy('date'); }
    public function activities(): HasMany { return $this->hasMany(BudgetActivity::class)->orderBy('date'); }
    public function memberBudgets(): HasMany { return $this->hasMany(MemberBudget::class); }

    public function getTotalIncomeAttribute(): float
    {
        return (float) $this->incomes()->sum('amount');
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->expenses()->where('type', 'actual')->sum('amount');
    }

    public function getTotalPlannedAttribute(): float
    {
        return (float) $this->expenses()->where('type', 'planned')->sum('amount');
    }

    public function getActivitiesTotalAttribute(): float
    {
        return (float) $this->activities()->get()
            ->sum(fn($a) => $a->price_per_person * $a->persons);
    }

    public function getRemainingAttribute(): float
    {
        return $this->total_income - $this->total_spent;
    }

    public function getBalanceAttribute(): float
    {
        // Revenus - dépenses réelles - dépenses prévues
        return $this->total_income - $this->total_spent - $this->total_planned;
    }
}
