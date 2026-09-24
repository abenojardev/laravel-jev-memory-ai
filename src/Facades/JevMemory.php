<?php

namespace Jev\Memory\Facades;

use Illuminate\Support\Facades\Facade;
use Jev\Memory\Support\MemoryManager;
use Jev\Memory\Support\ThreadManager;

class JevMemory extends Facade
{
    protected static function getFacadeAccessor(): string { return ThreadManager::class; }

    public static function threads(): ThreadManager { return app(ThreadManager::class); }
    public static function memories(): MemoryManager { return app(MemoryManager::class); }
}
