<?php

declare(strict_types=1);

namespace Rinvex\Menus\Tests\Unit;

use Collective\Html\HtmlServiceProvider;
use Rinvex\Menus\Providers\MenusServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class BaseTestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            HtmlServiceProvider::class,
            MenusServiceProvider::class,
        ];
    }
}
