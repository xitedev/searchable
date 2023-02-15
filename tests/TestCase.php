<?php

namespace Xite\Searchable\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Xite\Searchable\SearchableServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SearchableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
