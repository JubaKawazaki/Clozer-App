<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WearItem extends Pivot
{
    /* Pivot de la table wear_items : pièce portée lors d'un wear libre. */
    protected $fillable = [
        'wear_id',
        'item_id',
        'slot',
    ];

    protected $table = 'wear_items';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function wear(): BelongsTo
    {
        return $this->belongsTo(Wear::class);
    }
}
