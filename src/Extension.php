<?php

declare(strict_types=1);

namespace Tipowerup\Darkmode;

use Facades\Igniter\System\Helpers\SystemHelper;
use Igniter\Admin\Classes\MainMenuItem;
use Igniter\Admin\Classes\Navigation;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Flame\Support\Facades\Igniter;
use Igniter\System\Classes\BaseExtension;
use Igniter\System\Facades\Assets;
use Illuminate\Support\Facades\Event;
use Override;
use Tipowerup\Darkmode\Http\Middleware\InjectDarkmodeScript;
use Tipowerup\Darkmode\Livewire\DarkmodeToggle as LivewireDarkmodeToggle;
use Tipowerup\Darkmode\MainMenuWidgets\DarkmodeToggle as AdminDarkmodeToggle;
use Tipowerup\Darkmode\Models\Settings;

class Extension extends BaseExtension
{
    /**
     * Return extension metadata from the package root composer.json.
     *
     * TI resolves the config path from the Extension class file location,
     * which is `src/`. Our composer.json lives one level up at the package root.
     */
    #[Override]
    public function extensionMeta(): array
    {
        if (func_get_args()) {
            return $this->config = func_get_arg(0);
        }

        if (!is_null($this->config)) {
            return $this->config;
        }

        return $this->config = SystemHelper::extensionConfigFromFile(dirname(__DIR__));
    }

    public function boot(): void
    {
        $this->registerMiddleware();
        $this->registerAdminAssets();
        $this->registerFrontendAssets();
        $this->registerAdminToolbarToggle();
    }

    public function registerNavigation(): array
    {
        return [];
    }

    #[Override]
    public function registerPermissions(): array
    {
        return [
            'Tipowerup.Darkmode.ManageSettings' => [
                'description' => 'lang:tipowerup.darkmode::default.permission_manage_settings',
                'group' => 'module',
            ],
        ];
    }

    #[Override]
    public function registerSettings(): array
    {
        return [
            'settings' => [
                'label' => 'lang:tipowerup.darkmode::default.text_title',
                'description' => 'lang:tipowerup.darkmode::default.text_description',
                'icon' => 'fa fa-moon',
                'model' => Settings::class,
                'permissions' => ['Tipowerup.Darkmode.ManageSettings'],
            ],
        ];
    }

    #[Override]
    public function registerComponents(): array
    {
        return [
            LivewireDarkmodeToggle::class => [
                'code' => 'tipowerup-darkmode::darkmode-toggle',
                'name' => 'lang:tipowerup.darkmode::default.component_toggle_title',
                'description' => 'lang:tipowerup.darkmode::default.component_toggle_desc',
            ],
        ];
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', InjectDarkmodeScript::class);
    }

    protected function registerAdminAssets(): void
    {
        Event::listen('admin.controller.beforeRemap', function ($controller): void {
            if (!Settings::isEnabled() || !Settings::appliesToAdmin()) {
                return;
            }

            $controller->addJs('tipowerup.darkmode::/js/darkreader.min.js', 'darkreader-js');
            $controller->addJs('tipowerup.darkmode::/js/darkmode.js', 'darkmode-js');

            Assets::putJsVars(['tiDarkmode' => Settings::darkreaderConfig()]);
        });
    }

    protected function registerFrontendAssets(): void
    {
        Event::listen('main.controller.beforeRemap', function (): void {
            if (!Settings::isEnabled() || !Settings::appliesToFrontend()) {
                return;
            }

            Assets::addJs('tipowerup.darkmode::/js/darkreader.min.js', 'darkreader-js');
            Assets::addJs('tipowerup.darkmode::/js/darkmode.js', 'darkmode-js');

            Assets::putJsVars(['tiDarkmode' => Settings::darkreaderConfig()]);
        });
    }

    protected function registerAdminToolbarToggle(): void
    {
        if (!Igniter::runningInAdmin()) {
            return;
        }

        try {
            if (!Settings::isEnabled() || !Settings::appliesToAdmin() || !Settings::showAdminToolbarToggle()) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        AdminMenu::registerCallback(function (Navigation $manager): void {
            $manager->registerMainItems([
                MainMenuItem::widget('darkmode', AdminDarkmodeToggle::class)
                    ->priority(18)
                    ->permission('Tipowerup.Darkmode.ManageSettings'),
            ]);
        });
    }
}
