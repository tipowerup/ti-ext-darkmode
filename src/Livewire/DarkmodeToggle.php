<?php

declare(strict_types=1);

namespace Tipowerup\Darkmode\Livewire;

use Igniter\Main\Traits\ConfigurableComponent;
use Illuminate\View\View;
use Livewire\Component;
use Tipowerup\Darkmode\Models\Settings;

final class DarkmodeToggle extends Component
{
    use ConfigurableComponent;

    public string $class = 'btn btn-outline-secondary';

    public bool $showLabel = false;

    public string $darkLabel = '';

    public string $lightLabel = '';

    public static function componentMeta(): array
    {
        return [
            'code' => 'tipowerup-darkmode::darkmode-toggle',
            'name' => 'tipowerup.darkmode::default.component_toggle_title',
            'description' => 'tipowerup.darkmode::default.component_toggle_desc',
        ];
    }

    public function render(): View|string
    {
        if (!Settings::isEnabled() || !Settings::appliesToFrontend()) {
            return <<<'HTML'
            <span></span>
            HTML;
        }

        return view('tipowerup.darkmode::livewire.darkmode-toggle');
    }
}
