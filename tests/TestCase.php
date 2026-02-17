<?php

declare(strict_types=1);

namespace Tipowerup\Template\Tests;

use Tipowerup\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getExtensionBasePath(): string
    {
        return dirname(__DIR__);
    }

    protected function getExtensionProviders(): array
    {
        return [\Tipowerup\Template\Extension::class];
    }
}
