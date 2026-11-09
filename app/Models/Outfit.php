<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outfit extends Model
{
    /** @use HasFactory<\Database\Factories\OutfitFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_path',
        'last_worn_at',
    ];

    protected function casts(): array
    {
        return [
            'last_worn_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Pièces par slot : rangée Build rangée par position (les couches). */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'outfit_items')
            ->using(OutfitItem::class)
            ->withPivot('slot', 'position')
            ->withTimestamps();
    }

    public function wears(): HasMany
    {
        return $this->hasMany(Wear::class);
    }
}
