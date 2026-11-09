<?php

namespace App\Http\Controllers;

use App\Models\Wear;
use App\Models\Item;
use App\Models\Outfit;
use Illuminate\Http\JsonResponse;

class WearController extends Controller
{
    /** Récupérer le wear log de l'utilisateur connecté. */
    public function index(): JsonResponse
    {
        $wears = Wear::where('user_id', auth()->id())
            ->with(['items.item', 'outfit'])
            ->orderByDesc('worn_on')
            ->paginate(50);

        return WearResource::collection($wears);
    }

    /** Enregistrer un port de tenue ou de pièces libres. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worn_on' => 'required|date|date_format:Y-m-d',
            'outfit_id' => 'nullable|exists:outfits,id',
            'photo' => 'nullable|file|mimes:jpeg,png,webp|max:10240',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.slot' => 'nullable|in:headwear,outerwear,top,bottom,shoes',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('items/wears', 'local');
        }

        $wear = Wear::create([
            'user_id' => auth()->id(),
            'worn_on' => $validated['worn_on'],
            'outfit_id' => $validated['outfit_id'],
            'photo_path' => $photoPath,
        ]);

        // Attacher les items si présents
        if (isset($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                $wear->items()->attach($itemData['id'], [
                    'slot' => $itemData['slot'] ?? null,
                ]);
            }
        }

        // Mise à jour de lastWorn sur les items et la tenue
        $wear->syncLastWorn();

        return new WearResource($wear->load('items.item', 'outfit'), 201);
    }

    /** Obtenir un port spécifique. */
    public function show(Wear $wear): JsonResponse
    {
        $this->authorize('view', $wear);

        return new WearResource($wear->load('items.item', 'outfit'));
    }

    /** Supprimer un port. */
    public function destroy(Wear $wear): JsonResponse
    {
        $this->authorize('delete', $wear);

        $wear->delete();

        return response()->json(null, 204);
    }
}
