<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ImageService;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function __construct(private ImageService $images, private TagService $tags)
    {
    }

    /** Récupérer les items de l'utilisateur connecté, avec pagination. */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        $archived = $request->input('archived', false);

        $query = Item::where('user_id', auth()->id());

        if (!$archived) {
            $query->where('archived', false);
        }

        return ItemResource::collection($query->paginate($perPage));
    }

    /** Créer un item et stocker la photo (WebP + miniature). */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo' => 'required|file|mimes:jpeg,png,webp|max:10240',
            'name' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'weather' => 'nullable|array',
            'styles' => 'nullable|array',
            'dress' => 'nullable|boolean',
        ]);

        $path = $this->images->saveWebp($request->file('photo'), 'items/originals');
        $thumb = $this->images->saveWebp($request->file('photo'), 'items/thumbs', 300, 80);

        $item = Item::create([
            'user_id' => auth()->id(),
            'photo_path' => $path,
            'thumb_path' => $thumb,
            'name' => $validated['name'],
            'categories' => $validated['categories'] ?? ['top'],
            'weather' => $validated['weather'] ?? [],
            'styles' => $validated['styles'] ?? [],
            'dress' => $validated['dress'] ?? false,
        ]);

        // Persist tags in the normalized tables (back-compat columns stay in sync).
        $this->tags->setTags($item->id, 'category', $validated['categories'] ?? ['top']);
        $this->tags->setTags($item->id, 'weather', $validated['weather'] ?? []);
        $this->tags->setTags($item->id, 'style', $validated['styles'] ?? []);

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

        if (isset($validated['categories'])) {
            $this->tags->setTags($item->id, 'category', $validated['categories']);
        }
        if (isset($validated['weather'])) {
            $this->tags->setTags($item->id, 'weather', $validated['weather']);
        }
        if (isset($validated['styles'])) {
            $this->tags->setTags($item->id, 'style', $validated['styles']);
        }

        return new ItemResource($item);
    }

    /** Supprimer un item et ses images. */
    public function destroy(Item $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $this->images->removeImage($item->photo_path);
        $this->images->removeImage($item->thumb_path);

        $item->delete();

        return response()->json(null, 204);
    }
}