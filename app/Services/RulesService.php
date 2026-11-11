<?php

namespace App\Services;

/**
 * Every rule behind the daily suggestion and the outerwear row, in one place.
 * Ported from Closet server/src/rules.ts.
 */
class RulesService
{
    public const CATEGORIES = ['headwear', 'outerwear', 'top', 'bottom', 'shoes'];

    public const WEATHER_TAGS = ['hot', 'warm', 'cool', 'cold', 'rainy', 'windy'];

    public const TEMPERATURES = ['hot', 'warm', 'cool', 'cold'];

    /** Whether the daily pick looks at the weather at all. */
    public bool $useWeather = false;

    /** Skip a piece if worn within this many days (not counting today). */
    public array $recencyDays = [
        'top' => 5,
        'bottom' => 3,
        'outerwear' => 0,
        'headwear' => 0,
        'shoes' => 0,
    ];

    /** Temperature thresholds (°F). */
    public array $temperature = ['coldBelow' => 45, 'coolBelow' => 65, 'warmBelow' => 80];

    /** Rainy above this chance (%), windy above this wind speed (mph). */
    public int $rainyAboveChance = 50;
    public int $windyAboveMph = 20;

    /** Show outerwear row when high is below this (°F), or rainy/windy. */
    public int $outerwearBelowHigh = 60;
    public bool $outerwearWhenRainy = true;
    public bool $outerwearWhenWindy = true;

    /** On cold days, suggest a hat tagged cold if there is one. */
    public bool $hatWhenCold = true;

    /** How many of the best-ranked saved outfits the day's pick is drawn from. */
    public int $outfitShortlist = 3;

    public function temperatureFor(int $high): string
    {
        $t = $this->temperature;
        if ($high < $t['coldBelow']) return 'cold';
        if ($high < $t['coolBelow']) return 'cool';
        if ($high < $t['warmBelow']) return 'warm';
        return 'hot';
    }

    /**
     * Today's conditions: temperature plus 'rainy'/'windy' when they apply.
     *
     * @return array<string>
     */
    public function conditionsFor(int $high, int $rainChance, int $windMph): array
    {
        $c = [$this->temperatureFor($high)];
        if ($rainChance > $this->rainyAboveChance) $c[] = 'rainy';
        if ($windMph > $this->windyAboveMph) $c[] = 'windy';
        return $c;
    }

    public function needsOuterwear(int $high, array $conditions): bool
    {
        return $high < $this->outerwearBelowHigh
            || ($this->outerwearWhenRainy && in_array('rainy', $conditions))
            || ($this->outerwearWhenWindy && in_array('windy', $conditions));
    }

    /** Whether a piece's weather tags allow today's temperature. */
    public function suitsTemperature(array $tags, string $today): bool
    {
        $temps = array_filter($tags, fn($t) => in_array($t, self::TEMPERATURES));
        return count($temps) === 0 || in_array($today, $temps);
    }
}