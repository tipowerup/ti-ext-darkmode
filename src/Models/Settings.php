<?php

declare(strict_types=1);

namespace Tipowerup\Darkmode\Models;

use Igniter\Flame\Database\Model;
use Igniter\System\Actions\SettingsModel;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool set(string|array $key, mixed $value = null)
 *
 * @mixin SettingsModel
 */
class Settings extends Model
{
    public array $implement = [SettingsModel::class];

    public string $settingsCode = 'tipowerup_darkmode_settings';

    public string $settingsFieldsConfig = 'settings';

    public static function isEnabled(): bool
    {
        return (bool) self::get('is_enabled', false);
    }

    public static function appliesTo(): string
    {
        return (string) self::get('apply_to', 'both');
    }

    public static function appliesToAdmin(): bool
    {
        return in_array(self::appliesTo(), ['admin', 'both'], true);
    }

    public static function appliesToFrontend(): bool
    {
        return in_array(self::appliesTo(), ['frontend', 'both'], true);
    }

    public static function showAdminToolbarToggle(): bool
    {
        return (bool) self::get('admin_toolbar_toggle', true);
    }

    /**
     * @return array{brightness: int, contrast: int, sepia: int, schedule_enabled: bool, schedule_type: string, start_time: string, end_time: string, latitude: string, longitude: string}
     */
    public static function darkreaderConfig(): array
    {
        return [
            'brightness' => (int) self::get('brightness', 100),
            'contrast' => (int) self::get('contrast', 90),
            'sepia' => (int) self::get('sepia', 10),
            'schedule_enabled' => (bool) self::get('schedule_enabled', false),
            'schedule_type' => (string) self::get('schedule_type', 'time'),
            'start_time' => (string) self::get('start_time', '20:00'),
            'end_time' => (string) self::get('end_time', '06:00'),
            'latitude' => (string) self::get('latitude', ''),
            'longitude' => (string) self::get('longitude', ''),
        ];
    }

    public static function shouldScheduleBeActive(): bool
    {
        if (!(bool) self::get('schedule_enabled', false)) {
            return false;
        }

        if ((string) self::get('schedule_type', 'time') === 'sunset_sunrise') {
            return self::isAfterSunset(
                (string) self::get('latitude', ''),
                (string) self::get('longitude', '')
            );
        }

        return self::isInScheduleTimeRange(
            (string) self::get('start_time', '20:00'),
            (string) self::get('end_time', '06:00')
        );
    }

    private static function isInScheduleTimeRange(string $startTime, string $endTime): bool
    {
        $start = explode(':', $startTime);
        $end = explode(':', $endTime);

        $startMinutes = (int) ($start[0] ?? 0) * 60 + (int) ($start[1] ?? 0);
        $endMinutes = (int) ($end[0] ?? 0) * 60 + (int) ($end[1] ?? 0);

        $now = new \DateTime;
        $currentMinutes = (int) $now->format('H') * 60 + (int) $now->format('i');

        // Handle overnight range (e.g., 20:00 → 06:00)
        if ($startMinutes > $endMinutes) {
            return $currentMinutes >= $startMinutes || $currentMinutes < $endMinutes;
        }

        return $currentMinutes >= $startMinutes && $currentMinutes < $endMinutes;
    }

    private static function isAfterSunset(string $latitude, string $longitude): bool
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;

        if ($latitude === '' || $longitude === '') {
            return false;
        }

        $now = new \DateTime;
        $sunTimes = self::calcSunTimes($lat, $lng, $now);

        $currentMinutes = (int) $now->format('H') * 60 + (int) $now->format('i');

        // Dark between sunset and sunrise (overnight)
        return $currentMinutes >= $sunTimes['sunset'] || $currentMinutes < $sunTimes['sunrise'];
    }

    /**
     * NOAA solar calculation algorithm.
     * Returns sunrise and sunset times in minutes from midnight, normalized to [0, 1440).
     *
     * IMPORTANT: Duplicated in resources/js/darkmode.js::calcSunTimes().
     * Changes here must be mirrored there.
     *
     * @param  float  $lat  Latitude
     * @param  float  $lng  Longitude
     * @param  \DateTime  $date  Date to calculate for
     * @return array{sunrise: float, sunset: float}
     */
    private static function calcSunTimes(float $lat, float $lng, \DateTime $date): array
    {
        $rad = M_PI / 180;

        // Calculate day of year
        $startOfYear = new \DateTime($date->format('Y').'-01-01');
        $dayOfYear = (int) $date->diff($startOfYear)->format('%a') + 1;

        $gamma = (2 * M_PI / 365) * ($dayOfYear - 1);

        // Equation of time in minutes
        $eqTime = 229.18 * (0.000075 + 0.001868 * cos($gamma)
            - 0.032077 * sin($gamma)
            - 0.014615 * cos(2 * $gamma)
            - 0.040849 * sin(2 * $gamma));

        // Solar declination in radians
        $decl = 0.006918 - 0.399912 * cos($gamma) + 0.070257 * sin($gamma)
            - 0.006758 * cos(2 * $gamma) + 0.000907 * sin(2 * $gamma)
            - 0.002697 * cos(3 * $gamma) + 0.00148 * sin(3 * $gamma);

        $latRad = $lat * $rad;
        $cosZenith = cos(90.833 * $rad);
        $cosHA = ($cosZenith / (cos($latRad) * cos($decl))) - tan($latRad) * tan($decl);

        // Handle polar night and polar day
        if ($cosHA > 1) {
            // Sun never rises (polar night) - always dark
            return ['sunrise' => 1440, 'sunset' => 0];
        }
        if ($cosHA < -1) {
            // Sun never sets (polar day) - always light
            return ['sunrise' => 0, 'sunset' => 1440];
        }

        $hourAngle = acos($cosHA);

        // Calculate sunrise and sunset in UTC minutes
        $sunriseMin = 720 - 4 * ($lng + $hourAngle / $rad) - $eqTime;
        $sunsetMin = 720 - 4 * ($lng - $hourAngle / $rad) - $eqTime;

        // Convert UTC to local time using PHP's timezone offset
        $tzOffsetSeconds = (int) $date->format('Z');
        $tzOffsetMinutes = $tzOffsetSeconds / 60;

        return [
            'sunrise' => fmod($sunriseMin + $tzOffsetMinutes + 1440, 1440),
            'sunset' => fmod($sunsetMin + $tzOffsetMinutes + 1440, 1440),
        ];
    }
}
