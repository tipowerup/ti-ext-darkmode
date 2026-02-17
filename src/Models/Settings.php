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
        $applyTo = self::appliesTo();

        return $applyTo === 'admin' || $applyTo === 'both';
    }

    public static function appliesToFrontend(): bool
    {
        $applyTo = self::appliesTo();

        return $applyTo === 'frontend' || $applyTo === 'both';
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
}
