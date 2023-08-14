<?php

namespace Xite\Searchable\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Xite\Searchable\SearchableServiceProvider;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }

    protected function getPackageProviders($app): array
    {
        return [
            SearchableServiceProvider::class,
        ];
    }
}
