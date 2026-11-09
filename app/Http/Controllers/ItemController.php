<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    /** Récupérer les items de l'utilisateur connecté, avec pagination. */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 50);
        $archived = $request->input('archived', false);

        $query = Item::where('user_id', auth()->id());

        if (!$archived) {
            $query->where('archived', false);
        }

        return ItemResource::collection($query->paginate($perPage));
    }

    /** Créer un item et stocker la photo sur le disque local. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo' => 'required|file|mimes:jpeg,png,webp|max:10240', // 10MB
            'name' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'weather' => 'nullable|array',
            'styles' => 'nullable|array',
            'dress' => 'nullable|boolean',
        ]);

        $path = $request->file('photo')->store('items/originals', 'local');

        $item = Item::create([
            'user_id' => auth()->id(),
            'photo_path' => $path,
            'name' => $validated['name'],
            'categories' => $validated['categories'] ?? ['top'],
            'weather' => $validated['weather'] ?? [],
            'styles' => $validated['styles'] ?? [],
            'dress' => $validated['dress'] ?? false,
        ]);

        return new ItemResource($item, 201);
    }

    /** Obtenir un item par ID. */
    public function show(Item $item): JsonResponse
    {
        $this->authorize('view', $item);

        return new ItemResource($item);
    }

    /** Mettre à jour un item. */
    public function update(Request $request, Item $item): JsonResponse
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'weather' => 'nullable|array',
            'styles' => 'nullable|array',
            'dress' => 'nullable|boolean',
            'archived' => 'nullable|boolean',
        ]);

        $item->update($validated);

        return new ItemResource($item);
    }

    /** Supprimer un item. */
    public function destroy(Item $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return response()->json(null, 204);
    }
}
