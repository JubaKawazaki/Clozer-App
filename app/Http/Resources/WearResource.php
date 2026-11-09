<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'worn_on' => $this->worn_on,
            'outfit_id' => $this->outfit_id,
            'photo_path' => $this->photo_path,
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'slot' => $item->pivot->slot,
                'item' => new ItemResource($item->item),
            ]),
            'outfit' => $this->outfit ? new OutfitResource($this->outfit) : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
