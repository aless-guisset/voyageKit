<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripMember extends Model
{
    protected $fillable = ['trip_group_id', 'name', 'avatar_emoji', 'color', 'role', 'age'];

    const ROLE_LABELS = [
        'adult' => 'Adulte',
        'teen'  => 'Ado',
        'child' => 'Enfant',
        'baby'  => 'Bébé',
    ];

    public function group(): BelongsTo { return $this->belongsTo(TripGroup::class, 'trip_group_id'); }

    public function packingLists(): HasMany
    {
        return $this->hasMany(PackingList::class);
    }

    public function memberBudget(): HasOne
    {
        return $this->hasOne(MemberBudget::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(BudgetActivity::class, 'activity_participants', 'trip_member_id', 'budget_activity_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }
}
