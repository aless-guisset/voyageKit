<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListTemplate extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'icon', 'is_public'];
    protected $casts = ['is_public' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(ListTemplateItem::class)->orderBy('category')->orderBy('sort_order'); }
}
