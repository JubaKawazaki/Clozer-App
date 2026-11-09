<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OutfitItem extends Pivot
{
    /* Pivot de la table outfit_items : un item dans une tenue, dans un
     * slot (rangée Build) à une position de couche donnée. */

    protected $fillable = [
        'outfit_id',
        'item_id',
        'slot',
        'position',
    ];

    protected $table = 'outfit_items';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function outfit(): BelongsTo
    {
        return $this->belongsTo(Outfit::class);
    }
}
