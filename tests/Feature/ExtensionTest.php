<?php

declare(strict_types=1);

use Tipowerup\Template\Extension;

it('boots the extension', function (): void {
    $extension = new Extension($this->app);

    expect($extension)->toBeInstanceOf(Extension::class);
});
