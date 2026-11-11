<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\TagService;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tags = app(TagService::class)->loadTags([$this->resource->id])[$this->resource->id] ?? null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'photo_path' => $this->photo_path,
            'thumb_path' => $this->thumb_path,
            'categories' => $tags['categories'] ?? $this->categories ?? [],
            'weather' => $tags['weather'] ?? $this->weather ?? [],
            'styles' => $tags['styles'] ?? $this->styles ?? [],
            'dress' => $this->dress,
            'archived' => $this->archived,
            'last_worn_at' => $this->last_worn_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}