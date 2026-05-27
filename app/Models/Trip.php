<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'destination', 'start_date', 'end_date',
        'cover_emoji', 'notes', 'status',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
    protected $appends = ['duration_days', 'days_until'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function itineraryDays(): HasMany { return $this->hasMany(ItineraryDay::class)->orderBy('date'); }
    public function packingLists(): HasMany { return $this->hasMany(PackingList::class); }
    public function budget(): HasOne { return $this->hasOne(Budget::class); }

    public function groups(): HasMany
    {
        return $this->hasMany(TripGroup::class)->with('members');
    }

    // Tous les membres du voyage (via les groupes)
    public function allMembers()
    {
        return TripMember::whereHas('group', fn($q) => $q->where('trip_id', $this->id));
    }

    public function getDurationDaysAttribute(): ?int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return null;
    }

    public function getDaysUntilAttribute(): ?int
    {
        if ($this->start_date) {
            return (int) now()->startOfDay()->diffInDays($this->start_date->startOfDay(), false);
        }
        return null;
    }
}
