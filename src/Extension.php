<?php

declare(strict_types=1);

namespace Tipowerup\Template;

use Facades\Igniter\System\Helpers\SystemHelper;
use Igniter\System\Classes\BaseExtension;
use Override;

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

    /**
     * Register extension services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Boot extension after all services are registered.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register admin navigation menu items.
     */
    public function registerNavigation(): array
    {
        return [];
    }

    /**
     * Register backend permissions.
     */
    public function registerPermissions(): array
    {
        return [];
    }

    /**
     * Register extension settings.
     */
    public function registerSettings(): array
    {
        return [];
    }
}
