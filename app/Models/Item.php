<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    /**
     * [PLACEHOLDER:rembg] — traitement des photos (coupe du fond, vignette) en
     * réarrière-plan, arrivé plus tard via une file d'attente ; un item
     * d'ici-là n'a que photo_path (le photo_path original reste inchangé).
     */
    protected $fillable = [
        'name',
        'photo_path',
        'thumb_path',
        'categories',
        'weather',
        'styles',
        'dress',
        'archived',
        'last_worn_at',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'weather' => 'array',
            'styles' => 'array',
            'dress' => 'boolean',
            'archived' => 'boolean',
            'last_worn_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outfits(): BelongsToMany
    {
        return $this->belongsToMany(Outfit::class, 'outfit_items')
            ->using(OutfitItem::class)
            ->withPivot('slot', 'position')
            ->withTimestamps();
    }

    /** Miroir lastWorn : avance seulement si le port est plus récent. */
    public function updateLastWorn(\DateTimeInterface|string $when): void
    {
        $date = \Illuminate\Support\Carbon::parse($when)->toDateString();

        if ($this->last_worn_at === null || $this->last_worn_at->lt($date)) {
            $this->forceFill(['last_worn_at' => $date])->save();
        }
    }
}
