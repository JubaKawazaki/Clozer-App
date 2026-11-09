<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'photo_path' => $this->photo_path,
            'last_worn_at' => $this->last_worn_at?->toIso8601String(),
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'slot' => $item->pivot->slot,
                'position' => $item->pivot->position,
                'item' => new ItemResource($item->item),
            ]),
            'wears_count' => $this->wears()->count(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
