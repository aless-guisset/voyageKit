<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberBudget extends Model
{
    protected $fillable = ['budget_id', 'trip_member_id', 'allocated_amount', 'personal_spending'];
    protected $casts = ['allocated_amount' => 'decimal:2', 'personal_spending' => 'decimal:2'];

    public function budget(): BelongsTo { return $this->belongsTo(Budget::class); }
    public function member(): BelongsTo { return $this->belongsTo(TripMember::class, 'trip_member_id'); }
}
