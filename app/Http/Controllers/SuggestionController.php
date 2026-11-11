<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Outfit;
use App\Models\Wear;
use App\Services\RulesService;
use App\Services\SuggestService;
use App\Services\TagService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;

class SuggestionController extends Controller
{
    public function __construct(
        private RulesService $rules,
        private SuggestService $suggest,
        private WeatherService $weather,
        private TagService $tags,
    ) {
    }

    /** GET /api/suggestion — la suggestion du jour pour l'utilisateur connecté. */
    public function __invoke(): JsonResponse
    {
        $user = auth()->user();
        $date = today()->toDateString();

        // Météo (nullable : sans météo, on retombe sur "cool, sec").
        $weather = $this->weather->getWeather();

        // Items visibles (non archivés, avec catégories).
        $items = Item::where('user_id', $user->id)
            ->where('archived', false)
            ->get()
            ->filter(fn($i) => count($i->categories) > 0)
            ->values();

        $tagMap = $this->tags->loadTags($items->pluck('id')->toArray());

        $items = $items->map(function ($i) use ($tagMap) {
            $t = $tagMap[$i->id] ?? ['categories' => [], 'weather' => [], 'styles' => []];
            return [
                'id' => $i->id,
                'categories' => $t['categories'],
                'weather' => $t['weather'],
                'lastWorn' => $i->last_worn_at?->toDateString(),
                'dress' => $i->dress,
                'styles' => $t['styles'],
            ];
        })->values()->all();

        // Tenues (avec pièces par slot, en ordre de couches).
        $outfits = Outfit::where('user_id', $user->id)
            ->with(['items' => fn($q) => $q->orderBy('position')])
            ->get()
            ->map(function ($o) {
                return [
                    'id' => $o->id,
                    'pieces' => $o->items->map(fn($i) => [
                        'id' => $i->id,
                        'slot' => $i->pivot->slot,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        $conditions = $weather['conditions'] ?? ['cool'];
        $needsOuterwear = $weather['needsOuterwear'] ?? false;

        $result = $this->suggest->suggest([
            'person' => (string) $user->id,
            'date' => $date,
            'conditions' => $conditions,
            'needsOuterwear' => $needsOuterwear,
            'items' => $items,
            'outfits' => $outfits,
            'useWeather' => $this->rules->useWeather,
        ]);

        // Enrichir avec les items complets (pour l'affichage).
        $selectedIds = [];
        foreach ($result['selected'] as $ids) {
            foreach ($ids as $id) $selectedIds[] = $id;
        }
        $selectedItems = Item::whereIn('id', $selectedIds)->get()->keyBy('id');

        return response()->json([
            ...$result,
            'weather' => $weather,
            'selected_items' => collect($result['selected'])->map(function ($ids) use ($selectedItems) {
                return collect($ids)->map(fn($id) => $selectedItems[$id] ?? null)->filter()->values()->all();
            })->all(),
        ]);
    }
}