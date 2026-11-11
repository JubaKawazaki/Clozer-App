<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Today's weather from Open-Meteo. Cached 30 min, stale OK 6 h.
 * Ported from Closet server/src/weather.ts.
 */
class WeatherService
{
    private const TTL_MS = 30 * 60;
    private const STALE_OK_MS = 6 * 60 * 60;

    public function __construct(private RulesService $rules)
    {
    }

    /** WMO weather codes → a few plain words. */
    public function describe(int $code): string
    {
        if ($code === 0) return 'Clear';
        if ($code <= 2) return 'Partly cloudy';
        if ($code === 3) return 'Cloudy';
        if ($code === 45 || $code === 48) return 'Foggy';
        if ($code >= 51 && $code <= 57) return 'Drizzle';
        if ($code >= 61 && $code <= 67) return 'Rain';
        if ($code >= 71 && $code <= 77) return 'Snow';
        if ($code >= 80 && $code <= 82) return 'Showers';
        if ($code === 85 || $code === 86) return 'Snow showers';
        if ($code >= 95) return 'Thunderstorms';
        return 'Mixed';
    }

    /**
     * @return array|null Weather, or null if unavailable.
     */
    public function getWeather(?float $lat = null, ?float $lon = null): ?array
    {
        $lat = $lat ?? (float) env('LAT', 40.7128);
        $lon = $lon ?? (float) env('LON', -74.006);

        return Cache::remember('weather', self::TTL_MS, function () use ($lat, $lon) {
            try {
                $resp = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'temperature_2m,weather_code',
                    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_probability_max,wind_speed_10m_max,weather_code',
                    'wind_speed_unit' => 'mph',
                    'temperature_unit' => 'fahrenheit',
                    'timezone' => 'auto',
                    'forecast_days' => '1',
                ]);
                if (!$resp->ok()) throw new \Exception("Open-Meteo answered {$resp->status()}");
                $j = $resp->json();

                $high = (int) round($j['daily']['temperature_2m_max'][0] ?? 0);
                $precipChance = (int) ($j['daily']['precipitation_probability_max'][0] ?? 0);
                $code = (int) ($j['current']['weather_code'] ?? ($j['daily']['weather_code'][0] ?? 0));
                $wind = (int) round($j['daily']['wind_speed_10m_max'][0] ?? 0);

                return [
                    'date' => $j['daily']['time'][0] ?? today()->toDateString(),
                    'current' => isset($j['current']['temperature_2m']) ? (int) round($j['current']['temperature_2m']) : null,
                    'high' => $high,
                    'low' => (int) round($j['daily']['temperature_2m_min'][0] ?? 0),
                    'precipChance' => $precipChance,
                    'code' => $code,
                    'wind' => $wind,
                    'summary' => $this->describe($code),
                    'temperature' => $this->rules->temperatureFor($high),
                    'conditions' => $this->rules->conditionsFor($high, $precipChance, $wind),
                    'needsOuterwear' => $this->rules->needsOuterwear($high, $this->rules->conditionsFor($high, $precipChance, $wind)),
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}