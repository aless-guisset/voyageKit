<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListTemplateItem extends Model
{
    protected $fillable = ['list_template_id', 'name', 'category', 'quantity', 'unit', 'unit_price', 'need_to_buy', 'sort_order'];
    protected $casts = ['need_to_buy' => 'boolean', 'unit_price' => 'decimal:2', 'quantity' => 'decimal:2'];

    public function template(): BelongsTo { return $this->belongsTo(ListTemplate::class, 'list_template_id'); }
}
