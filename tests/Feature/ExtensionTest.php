<?php

declare(strict_types=1);

use Igniter\System\Actions\SettingsModel;
use Illuminate\Support\Facades\DB;
use Tipowerup\Darkmode\Extension;
use Tipowerup\Darkmode\Http\Middleware\InjectDarkmodeScript;
use Tipowerup\Darkmode\Livewire\DarkmodeToggle;
use Tipowerup\Darkmode\Models\Settings;

beforeEach(function (): void {
    SettingsModel::clearInternalCache();
    Settings::flushEventListeners();
});

it('boots the extension', function (): void {
    $extension = new Extension($this->app);

    expect($extension)->toBeInstanceOf(Extension::class);
});

it('registers settings', function (): void {
    $extension = new Extension($this->app);
    $settings = $extension->registerSettings();

    expect($settings)->toHaveKey('settings')
        ->and($settings['settings'])->toHaveKey('label')
        ->and($settings['settings'])->toHaveKey('icon')
        ->and($settings['settings']['icon'])->toBe('fa fa-moon')
        ->and($settings['settings']['model'])->toBe(Settings::class);
});

it('registers permissions', function (): void {
    $extension = new Extension($this->app);
    $permissions = $extension->registerPermissions();

    expect($permissions)->toHaveKey('Tipowerup.Darkmode.ManageSettings')
        ->and($permissions['Tipowerup.Darkmode.ManageSettings'])->toHaveKey('description');
});

it('registers components', function (): void {
    $extension = new Extension($this->app);
    $components = $extension->registerComponents();

    expect($components)->toHaveKey(DarkmodeToggle::class)
        ->and($components[DarkmodeToggle::class]['code'])->toBe('tipowerup-darkmode::darkmode-toggle');
});

it('configures settings model correctly', function (): void {
    $settings = new Settings;

    expect($settings->implement)->toBe([SettingsModel::class])
        ->and($settings->settingsCode)->toBe('tipowerup_darkmode_settings')
        ->and($settings->settingsFieldsConfig)->toBe('settings');
});

it('returns correct darkreader config defaults', function (): void {
    $config = Settings::darkreaderConfig();

    expect($config)->toHaveKey('brightness')
        ->and($config['brightness'])->toBe(100)
        ->and($config['contrast'])->toBe(90)
        ->and($config['sepia'])->toBe(10)
        ->and($config['schedule_enabled'])->toBeFalse();
});

it('reports as disabled by default', function (): void {
    expect(Settings::isEnabled())->toBeFalse();
});

it('reports apply_to defaults to both', function (): void {
    expect(Settings::appliesTo())->toBe('both')
        ->and(Settings::appliesToAdmin())->toBeTrue()
        ->and(Settings::appliesToFrontend())->toBeTrue();
});

it('shows admin toolbar toggle by default', function (): void {
    expect(Settings::showAdminToolbarToggle())->toBeTrue();
});

it('reports schedule as inactive by default', function (): void {
    expect(Settings::shouldScheduleBeActive())->toBeFalse();
});

it('reports time-based schedule as active during dark period', function (): void {
    // Calculate time range that includes current time
    $now = new DateTime;
    $currentHour = (int) $now->format('H');
    $currentMinute = (int) $now->format('i');

    // Set start time 2 hours before now
    $startHour = ($currentHour - 2 + 24) % 24;
    $startTime = sprintf('%02d:%02d', $startHour, $currentMinute);

    // Set end time 2 hours after now
    $endHour = ($currentHour + 2) % 24;
    $endTime = sprintf('%02d:%02d', $endHour, $currentMinute);

    DB::table('extension_settings')->insert([
        'item' => 'tipowerup_darkmode_settings',
        'data' => json_encode([
            'schedule_enabled' => true,
            'schedule_type' => 'time',
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]),
    ]);

    SettingsModel::clearInternalCache();

    expect(Settings::shouldScheduleBeActive())->toBeTrue();
});

it('reports time-based schedule as inactive during light period', function (): void {
    // Calculate time range that does NOT include current time
    $now = new DateTime;
    $currentHour = (int) $now->format('H');
    $currentMinute = (int) $now->format('i');

    // Set start time 2 hours from now
    $startHour = ($currentHour + 2) % 24;
    $startTime = sprintf('%02d:%02d', $startHour, $currentMinute);

    // Set end time 4 hours from now
    $endHour = ($currentHour + 4) % 24;
    $endTime = sprintf('%02d:%02d', $endHour, $currentMinute);

    DB::table('extension_settings')->insert([
        'item' => 'tipowerup_darkmode_settings',
        'data' => json_encode([
            'schedule_enabled' => true,
            'schedule_type' => 'time',
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]),
    ]);

    SettingsModel::clearInternalCache();

    expect(Settings::shouldScheduleBeActive())->toBeFalse();
});

it('reads settings from database', function (): void {
    DB::table('extension_settings')->insert([
        'item' => 'tipowerup_darkmode_settings',
        'data' => json_encode([
            'is_enabled' => true,
            'brightness' => 80,
            'contrast' => 95,
            'sepia' => 5,
        ]),
    ]);

    SettingsModel::clearInternalCache();

    expect(Settings::isEnabled())->toBeTrue()
        ->and(Settings::darkreaderConfig()['brightness'])->toBe(80)
        ->and(Settings::darkreaderConfig()['contrast'])->toBe(95)
        ->and(Settings::darkreaderConfig()['sepia'])->toBe(5);
});

it('applies_to admin restricts to admin only', function (): void {
    DB::table('extension_settings')->insert([
        'item' => 'tipowerup_darkmode_settings',
        'data' => json_encode(['is_enabled' => true, 'apply_to' => 'admin']),
    ]);

    SettingsModel::clearInternalCache();

    expect(Settings::appliesToAdmin())->toBeTrue()
        ->and(Settings::appliesToFrontend())->toBeFalse();
});

it('applies_to frontend restricts to frontend only', function (): void {
    DB::table('extension_settings')->insert([
        'item' => 'tipowerup_darkmode_settings',
        'data' => json_encode(['is_enabled' => true, 'apply_to' => 'frontend']),
    ]);

    SettingsModel::clearInternalCache();

    expect(Settings::appliesToAdmin())->toBeFalse()
        ->and(Settings::appliesToFrontend())->toBeTrue();
});

describe('InjectDarkmodeScript middleware', function (): void {
    beforeEach(function (): void {
        $this->middleware = new InjectDarkmodeScript;
        $this->next = fn ($request) => $this->response;
    });

    it('injects anti-flicker script into HTML response when enabled', function (): void {
        DB::table('extension_settings')->insert([
            'item' => 'tipowerup_darkmode_settings',
            'data' => json_encode(['is_enabled' => true, 'apply_to' => 'both']),
        ]);
        SettingsModel::clearInternalCache();

        $request = Illuminate\Http\Request::create('/');
        $this->response = new Illuminate\Http\Response('<html><head><title>Test</title></head><body></body></html>');
        $this->response->headers->set('Content-Type', 'text/html');

        $result = $this->middleware->handle($request, $this->next);

        expect($result->getContent())->toContain('ti-dm-pending')
            ->and($result->getContent())->toContain('ti-dm-af')
            ->and($result->getContent())->toContain('__tiDmReady');
    });

    it('does not inject script when darkmode is disabled', function (): void {
        $request = Illuminate\Http\Request::create('/');
        $this->response = new Illuminate\Http\Response('<html><head></head><body></body></html>');
        $this->response->headers->set('Content-Type', 'text/html');

        $result = $this->middleware->handle($request, $this->next);

        expect($result->getContent())->not->toContain('ti-dm-pending');
    });

    it('does not inject script for non-HTML responses', function (): void {
        DB::table('extension_settings')->insert([
            'item' => 'tipowerup_darkmode_settings',
            'data' => json_encode(['is_enabled' => true, 'apply_to' => 'both']),
        ]);
        SettingsModel::clearInternalCache();

        $request = Illuminate\Http\Request::create('/api/test');
        $this->response = new Illuminate\Http\Response('{"data": true}');
        $this->response->headers->set('Content-Type', 'application/json');

        $result = $this->middleware->handle($request, $this->next);

        expect($result->getContent())->not->toContain('ti-dm-pending')
            ->and($result->getContent())->toBe('{"data": true}');
    });

    it('injects schedule hint into anti-flicker script when schedule is active', function (): void {
        // Calculate time range that includes current time
        $now = new DateTime;
        $currentHour = (int) $now->format('H');
        $currentMinute = (int) $now->format('i');

        // Set start time 2 hours before now
        $startHour = ($currentHour - 2 + 24) % 24;
        $startTime = sprintf('%02d:%02d', $startHour, $currentMinute);

        // Set end time 2 hours after now
        $endHour = ($currentHour + 2) % 24;
        $endTime = sprintf('%02d:%02d', $endHour, $currentMinute);

        DB::table('extension_settings')->insert([
            'item' => 'tipowerup_darkmode_settings',
            'data' => json_encode([
                'is_enabled' => true,
                'apply_to' => 'both',
                'schedule_enabled' => true,
                'schedule_type' => 'time',
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]),
        ]);
        SettingsModel::clearInternalCache();

        $request = Illuminate\Http\Request::create('/');
        $this->response = new Illuminate\Http\Response('<html><head><title>Test</title></head><body></body></html>');
        $this->response->headers->set('Content-Type', 'text/html');

        $result = $this->middleware->handle($request, $this->next);

        expect($result->getContent())->toContain('var s=true');
    });

    it('injects false schedule hint when schedule is inactive', function (): void {
        DB::table('extension_settings')->insert([
            'item' => 'tipowerup_darkmode_settings',
            'data' => json_encode([
                'is_enabled' => true,
                'apply_to' => 'both',
            ]),
        ]);
        SettingsModel::clearInternalCache();

        $request = Illuminate\Http\Request::create('/');
        $this->response = new Illuminate\Http\Response('<html><head><title>Test</title></head><body></body></html>');
        $this->response->headers->set('Content-Type', 'text/html');

        $result = $this->middleware->handle($request, $this->next);

        expect($result->getContent())->toContain('var s=false');
    });
});
