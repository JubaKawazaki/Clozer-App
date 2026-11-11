<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wear extends Model
{
    /** @use HasFactory<\Database\Factories\WearFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'worn_on',
        'outfit_id',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'worn_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outfit(): BelongsTo
    {
        return $this->belongsTo(Outfit::class);
    }

    /** Port libre (sans tenue enregistrée) : les pièces portées ce jour-là. */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'wear_items')
            ->using(WearItem::class)
            ->withTimestamps();
    }

    /**
     * Miroir de denormalisation de Closet : lastWorn sur chaque pièce et
     * tenue. À appeler après la création et l'attachement des items, jamais
     * comme hook (les pivots auraient manqué). Avance seulement vers le futur.
     */
    public function syncLastWorn(): void
    {
        if ($outfit = $this->outfit()->first()) {
            $outfit->updateLastWorn($this->worn_on);
            $outfit->items()->get()->each(fn (Item $item) => $item->updateLastWorn($this->worn_on));
        }
    }
}
