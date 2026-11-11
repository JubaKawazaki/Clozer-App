<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Tag management: categories, weather, styles.
 * Ported from Closet server/src/routes/items.ts (loadTags, tagList).
 */
class TagService
{
    public const CATEGORIES = ['headwear', 'outerwear', 'top', 'bottom', 'shoes'];
    public const WEATHER_TAGS = ['hot', 'warm', 'cool', 'cold', 'rainy', 'windy'];

    /**
     * Load tags for a set of items: each list in a stable order.
     *
     * @return array<int, array{categories: array, weather: array, styles: array}>
     */
    public function loadTags(array $itemIds): array
    {
        $out = [];
        foreach ($itemIds as $id) {
            $out[$id] = ['categories' => [], 'weather' => [], 'styles' => []];
        }
        if (empty($out)) return $out;

        $rows = DB::table('item_tags')
            ->whereIn('item_id', $itemIds)
            ->get();

        foreach ($rows as $r) {
            if (!isset($out[$r->item_id])) continue;
            if ($r->kind === 'category') $out[$r->item_id]['categories'][] = $r->value;
            elseif ($r->kind === 'weather') $out[$r->item_id]['weather'][] = $r->value;
            elseif ($r->kind === 'style') $out[$r->item_id]['styles'][] = $r->value;
        }

        foreach ($out as $id => $tags) {
            $tags['categories'] = $this->sortCategories($tags['categories']);
            $tags['weather'] = $this->sortWeather($tags['weather']);
            $out[$id] = $tags;
        }

        return $out;
    }

    public function sortCategories(array $list): array
    {
        usort($list, fn($a, $b) => array_search($a, self::CATEGORIES) <=> array_search($b, self::CATEGORIES));
        return array_values($list);
    }

    public function sortWeather(array $list): array
    {
        usort($list, fn($a, $b) => array_search($a, self::WEATHER_TAGS) <=> array_search($b, self::WEATHER_TAGS));
        return array_values($list);
    }

    /**
     * Validate a tag list from the client: an array of allowed, distinct values.
     *
     * @return array<string>|string  list of strings, or error string
     */
    public function tagList($value, ?array $allowed, string $label)
    {
        if (!is_array($value) || !array_all($value, fn($v) => is_string($v))) {
            return "{$label} must be a list";
        }
        $list = array_values(array_unique(array_filter(array_map('strval', $value), fn($v) => trim($v) !== '')));
        if ($allowed) {
            $bad = array_filter($list, fn($v) => !in_array($v, $allowed));
            if (!empty($bad)) return 'Unknown '.strtolower($label).': '.implode(', ', $bad);
        }
        return $list;
    }

    /**
     * Set tags for an item (kind = category|weather|style).
     */
    public function setTags(int $itemId, string $kind, array $values): void
    {
        DB::transaction(function () use ($itemId, $kind, $values) {
            DB::table('item_tags')->where('item_id', $itemId)->where('kind', $kind)->delete();
            foreach ($values as $v) {
                DB::table('item_tags')->insert(['item_id' => $itemId, 'kind' => $kind, 'value' => $v]);
            }
        });
    }

    /**
     * All styles, ordered by position then name.
     *
     * @return array<string>
     */
    public function allStyles(): array
    {
        return DB::table('styles')->orderBy('position')->pluck('name')->toArray();
    }

    /**
     * Add a style (sentence case, deduplicated).
     */
    public function addStyle(string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '' || strlen($name) > 24) {
            throw new \Exception('A style needs a short name');
        }
        $pretty = ucfirst($name);
        DB::table('styles')->insertOrIgnore([
            'name' => $pretty,
            'position' => DB::table('styles')->max('position') + 1,
        ]);
        return $this->allStyles();
    }
}