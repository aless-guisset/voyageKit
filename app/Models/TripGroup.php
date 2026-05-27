<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripGroup extends Model
{
    protected $fillable = ['trip_id', 'name', 'icon', 'notes'];

    public function trip(): BelongsTo { return $this->belongsTo(Trip::class); }

    public function members(): HasMany
    {
        return $this->hasMany(TripMember::class)->orderBy('name');
    }

    public function packingLists(): HasMany
    {
        return $this->hasMany(PackingList::class);
    }
}
