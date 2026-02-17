<?php

declare(strict_types=1);

namespace Tipowerup\Darkmode\MainMenuWidgets;

use Igniter\Admin\Classes\BaseMainMenuWidget;
use Override;

class DarkmodeToggle extends BaseMainMenuWidget
{
    #[Override]
    public function render(): string
    {
        return $this->makePartial('darkmodetoggle/darkmodetoggle');
    }
}
