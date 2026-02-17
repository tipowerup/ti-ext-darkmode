<?php

declare(strict_types=1);

namespace Tipowerup\Darkmode\Livewire;

use Igniter\Main\Traits\ConfigurableComponent;
use Illuminate\View\View;
use Livewire\Component;

final class DarkmodeToggle extends Component
{
    use ConfigurableComponent;

    public static function componentMeta(): array
    {
        return [
            'code' => 'tipowerup-darkmode::darkmode-toggle',
            'name' => 'tipowerup.darkmode::default.component_toggle_title',
            'description' => 'tipowerup.darkmode::default.component_toggle_desc',
        ];
    }

    public function render(): View
    {
        return view('tipowerup.darkmode::livewire.darkmode-toggle');
    }
}
