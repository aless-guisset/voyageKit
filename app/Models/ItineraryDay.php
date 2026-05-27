<?php
// app/Models/ItineraryDay.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItineraryDay extends Model
{
    protected $fillable = ['trip_id', 'date', 'title', 'notes', 'sort_order'];
    protected $casts = ['date' => 'date'];

    public function trip(): BelongsTo { return $this->belongsTo(Trip::class); }
    public function events(): HasMany { return $this->hasMany(ItineraryEvent::class)->orderBy('time_start')->orderBy('sort_order'); }
}
