<?php

namespace App\Http\Controllers;

use App\Models\Outfit;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class OutfitController extends Controller
{
    /** Récupérer les tenues de l'utilisateur connecté. */
    public function index(): JsonResponse
    {
        $outfits = Outfit::where('user_id', auth()->id())
            ->with(['items.item', 'wears'])
            ->orderByDesc('created_at')
            ->get();

        return OutfitResource::collection($outfits);
    }

    /** Créer une tenue avec des items. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.slot' => 'required|in:headwear,outerwear,top,bottom,shoes',
            'items.*.position' => 'nullable|integer|min:0|max:2',
        ]);

        // Extraire les items du request (le reste est géré par le pivot)
        $itemsData = $validated['items'];

        $outfit = Outfit::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
        ]);

        // Attacher les items avec leur slot et position
        foreach ($itemsData as $itemData) {
            $outfit->items()->attach($itemData['id'], [
                'slot' => $itemData['slot'],
                'position' => $itemData['position'] ?? 0,
            ]);
        }

        $outfit->load('items.item');

        return new OutfitResource($outfit, 201);
    }

    /** Obtenir une tenue avec ses items. */
    public function show(Outfit $outfit): JsonResponse
    {
        $this->authorize('view', $outfit);

        return new OutfitResource($outfit);
    }

    /** Mettre à jour une tenue. */
    public function update(Request $request, Outfit $outfit): JsonResponse
    {
        $this->authorize('update', $outfit);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.slot' => 'required|in:headwear,outerwear,top,bottom,shoes',
            'items.*.position' => 'nullable|integer|min:0|max:2',
        ]);

        if (isset($validated['items'])) {
            $outfit->items()->detach();
            foreach ($validated['items'] as $itemData) {
                $outfit->items()->attach($itemData['id'], [
                    'slot' => $itemData['slot'],
                    'position' => $itemData['position'] ?? 0,
                ]);
            }
        }

        $outfit->update(['name' => $validated['name']]);

        return new OutfitResource($outfit->load('items.item'));
    }

    /** Supprimer une tenue. */
    public function destroy(Outfit $outfit): JsonResponse
    {
        $this->authorize('delete', $outfit);

        $outfit->delete();

        return response()->json(null, 204);
    }
}
