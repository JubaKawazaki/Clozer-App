<?php

namespace App\Http\Controllers;

use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StylesController extends Controller
{
    public function __construct(private TagService $tags)
    {
    }

    /** GET /api/styles — all styles, ordered by position then name. */
    public function index(): JsonResponse
    {
        return response()->json($this->tags->allStyles());
    }

    /** POST /api/styles — add a style (sentence case, deduplicated). */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $styles = $this->tags->addStyle($validated['name']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($styles, 201);
    }
}