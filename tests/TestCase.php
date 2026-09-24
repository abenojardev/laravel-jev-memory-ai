<?php

namespace Jev\Memory\Tests;

use Jev\Memory\Laravel\JevMemoryServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [JevMemoryServiceProvider::class];
    }

}
