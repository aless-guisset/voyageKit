<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryEvent extends Model
{
    protected $fillable = [
        'itinerary_day_id','title','location','lat','lng','place_id',
        'time_start','time_end','type','icon','notes',
        'estimated_cost','toll_cost','travel_minutes','rest_minutes',
        'places_to_visit','travel_notes',
    ];

    protected $casts = [
        'estimated_cost'  => 'decimal:2',
        'toll_cost'       => 'decimal:2',
        'lat'             => 'decimal:7',
        'lng'             => 'decimal:7',
        'places_to_visit' => 'array',
    ];

    const TYPE_ICONS = [
        'activity'      => '🎯',
        'transport'     => '🚗',
        'accommodation' => '🏨',
        'food'          => '🍽️',
        'rest'          => '☕',
        'other'         => '📌',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
    }

    public function getIconAttribute($value): string
    {
        return $value ?: self::TYPE_ICONS[$this->type] ?? '📌';
    }

    public function getWazeLinkAttribute(): ?string
    {
        if ($this->lat && $this->lng) {
            return "https://waze.com/ul?ll={$this->lat},{$this->lng}&navigate=yes";
        }
        if ($this->location) {
            $q = urlencode($this->location);
            return "https://waze.com/ul?q={$q}&navigate=yes";
        }
        return null;
    }

    public function getGoogleMapsLinkAttribute(): ?string
    {
        if ($this->lat && $this->lng) {
            return "https://maps.google.com/?q={$this->lat},{$this->lng}";
        }
        if ($this->location) {
            $q = urlencode($this->location);
            return "https://maps.google.com/?q={$q}";
        }
        return null;
    }

    protected $appends = ['waze_link', 'google_maps_link'];
}
