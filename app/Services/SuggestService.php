<?php

namespace App\Services;

/**
 * The daily suggestion. Pure and deterministic: the same inputs on the same day give the
 * same pick (seeded by person + date), so the app shows one answer all day.
 * Ported from Closet server/src/suggest.ts.
 */
class SuggestService
{
    private const NEVER = 60;

    public function __construct(private RulesService $rules)
    {
    }

    public function daysBetween(string $from, string $to): int
    {
        return (int) round((strtotime($to.'T12:00:00Z') - strtotime($from.'T12:00:00Z')) / 86400);
    }

    /** mulberry32 seeded from an FNV-1a hash of the string. */
    public function seededRandom(string $seed): callable
    {
        $h = 2166136261;
        for ($i = 0; $i < strlen($seed); $i++) {
            $h = ($h ^ ord($seed[$i])) * 16777619 & 0xFFFFFFFF;
        }
        return function () use (&$h): float {
            $h = ($h + 0x6d2b79f5) & 0xFFFFFFFF;
            $t = $h;
            $t = (($t ^ ($t >> 15)) * (1 | $t)) & 0xFFFFFFFF;
            $t = ($t + (($t ^ ($t >> 7)) * (61 | $t))) & 0xFFFFFFFF;
            return (($t ^ ($t >> 14)) & 0xFFFFFFFF) / 4294967296;
        };
    }

    /**
     * @param array $input [
     *   'person' => string, 'date' => string, 'conditions' => string[],
     *   'needsOuterwear' => bool, 'items' => array, 'outfits' => array, 'useWeather' => bool|null
     * ]
     * @return array
     */
    public function suggest(array $input): array
    {
        $date = $input['date'];
        $items = $input['items'];
        $useWeather = $input['useWeather'] ?? true;
        $needsOuterwear = $useWeather && $input['needsOuterwear'];
        $rand = $this->seededRandom($input['person'].':'.$date);
        $pick = fn(array $list) => $list[floor($rand() * count($list))];
        $temp = 'cool';
        foreach (['hot', 'warm', 'cool', 'cold'] as $t) {
            if (in_array($t, $input['conditions'])) { $temp = $t; break; }
        }
        $extras = $useWeather ? array_values(array_filter($input['conditions'], fn($c) => $c === 'rainy' || $c === 'windy')) : [];

        // A piece rests (or rotates) by the strictest window among the rows it can go in.
        $window = fn(array $i) => max(0, ...array_map(fn($c) => $this->rules->recencyDays[$c], $i['categories']));
        $rotates = fn(array $i) => $window($i) > 0;
        $freshness = function (array $i) use ($window, $rotates, $date) {
            if (!$rotates($i) || !$i['lastWorn']) return self::NEVER;
            return min($this->daysBetween($i['lastWorn'], $date), self::NEVER);
        };
        $rested = function (array $i) use ($window, $date) {
            return !$i['lastWorn'] || $this->daysBetween($i['lastWorn'], $date) > $window($i);
        };
        $suits = function (array $i) use ($useWeather, $temp) {
            return !$useWeather || $this->rules->suitsTemperature($i['weather'], $temp);
        };
        $preferred = fn(array $i) => count(array_intersect($extras, $i['weather'])) > 0;
        $byFresh = fn($a, $b) => $freshness($b) - $freshness($a) ?: $a['id'] - $b['id'];

        $used = [];
        $rules = $this->rules;
        $style = null;
        $styled = function (array $i) use (&$style) {
            return $style === null || in_array($style, $i['styles'] ?? []);
        };
        $pickPiece = function (string $category, callable $extra = null) use (
            &$used, $items, $extras, $styled, $rested, $suits, $preferred, $byFresh, $pick, $rules
        ) {
            $extra = $extra ?? fn() => true;
            $inCat = array_values(array_filter($items, fn($i) => in_array($category, $i['categories']) && !in_array($i['id'], $used) && $extra($i)));
            $tiers = [
                count($extras) ? array_values(array_filter($inCat, fn($i) => $styled($i) && $rested($i) && $suits($i) && $preferred($i))) : [],
                array_values(array_filter($inCat, fn($i) => $styled($i) && $rested($i) && $suits($i))),
                count($extras) ? array_values(array_filter($inCat, fn($i) => $rested($i) && $suits($i) && $preferred($i))) : [],
                array_values(array_filter($inCat, fn($i) => $rested($i) && $suits($i))),
                array_values(array_filter($inCat, fn($i) => $rested($i))),
                $inCat,
            ];
            $tier = null;
            foreach ($tiers as $t) { if (count($t)) { $tier = $t; break; } }
            if (!$tier) return [];
            $ranked = $tier;
            usort($ranked, $byFresh);
            $shortlist = $rules->recencyDays[$category] > 0
                ? array_slice($ranked, 0, min(5, max(1, (int) ceil(count($ranked) / 2))))
                : $ranked;
            $chosen = $pick($shortlist);
            $used[] = $chosen['id'];
            return [$chosen['id']];
        };

        // 1. A saved outfit where every piece passes the rules, favouring the least recently worn.
        $byId = [];
        foreach ($items as $i) $byId[$i['id']] = $i;
        $candidates = [];
        foreach ($input['outfits'] as $o) {
            $pieces = array_map(fn($p) => [...$p, 'item' => $byId[$p['id']] ?? null], $o['pieces']);
            if (count($pieces) === 0) continue;
            $ok = true;
            foreach ($pieces as $p) {
                if (!$p['item'] || !$rested($p['item']) || !$suits($p['item'])) { $ok = false; break; }
            }
            if (!$ok) continue;
            $its = array_map(fn($p) => $p['item'], $pieces);
            $score = array_sum(array_map($freshness, $its)) / count($its)
                + (count($extras) ? (10 * count(array_filter($its, $preferred))) / count($its) : 0);
            $candidates[] = ['o' => $o, 'score' => $score];
        }
        usort($candidates, fn($a, $b) => $b['score'] - $a['score'] ?: $a['o']['id'] - $b['o']['id']);

        if (count($candidates)) {
            $chosen = $pick(array_slice($candidates, 0, $this->rules->outfitShortlist))['o'];
            $selected = ['headwear' => [], 'outerwear' => [], 'top' => [], 'bottom' => [], 'shoes' => []];
            foreach ($chosen['pieces'] as $p) {
                $selected[$p['slot']][] = $p['id'];
                $used[] = $p['id'];
            }
            $withDress = false;
            foreach ($chosen['pieces'] as $p) { if ($byId[$p['id']]['dress'] ?? false) $withDress = true; }
            if ($needsOuterwear && count($selected['outerwear']) === 0 && !$withDress) {
                $selected['outerwear'] = $pickPiece('outerwear');
            }
            return [
                'date' => $date,
                'source' => 'outfit',
                'outfitId' => $chosen['id'],
                'selected' => $selected,
                'showOuterwear' => $needsOuterwear || count($selected['outerwear']) > 0,
            ];
        }

        // 2. Otherwise assemble one, row by row, with the same rules (never the same piece twice).
        $selected = ['headwear' => [], 'outerwear' => [], 'top' => [], 'bottom' => [], 'shoes' => []];
        $selected['top'] = $pickPiece('top');
        $top = $selected['top'][0] !== null ? ($byId[$selected['top'][0]] ?? null) : null;
        if ($top && count($top['styles'] ?? [])) $style = $pick($top['styles']);
        $dress = (bool) ($top['dress'] ?? false);
        if (!$dress) $selected['bottom'] = $pickPiece('bottom');
        $selected['shoes'] = $pickPiece('shoes');
        if ($needsOuterwear && !$dress) $selected['outerwear'] = $pickPiece('outerwear');
        if ($useWeather && $temp === 'cold' && $this->rules->hatWhenCold) {
            $coldHat = fn(array $i) => in_array('cold', $i['weather']);
            foreach ($items as $i) {
                if (in_array('headwear', $i['categories']) && $coldHat($i) && $rested($i)) {
                    $selected['headwear'] = $pickPiece('headwear', $coldHat);
                    break;
                }
            }
        }
        $any = count(array_filter($selected, fn($c) => count($c) > 0)) > 0;
        return [
            'date' => $date,
            'source' => $any ? 'pieces' : 'empty',
            'outfitId' => null,
            'selected' => $selected,
            'showOuterwear' => $needsOuterwear,
        ];
    }
}