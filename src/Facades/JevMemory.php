<?php

namespace Jev\Memory\Facades;

use Illuminate\Support\Facades\Facade;
use Jev\Memory\Support\MemoryManager;
use Jev\Memory\Support\ThreadManager;
use Jev\Memory\Support\JevMemoryFake;
use Jev\Memory\Support\RetentionManager;

class JevMemory extends Facade
{
    protected static function getFacadeAccessor(): string { return ThreadManager::class; }

    public static function threads(): ThreadManager { return app(ThreadManager::class); }
    public static function memories(): MemoryManager { return app(MemoryManager::class); }
    public static function retention(): RetentionManager { return app(RetentionManager::class); }
    public static function fake(): JevMemoryFake { return new JevMemoryFake(); }
    public static function assertState(\Jev\Memory\Models\Thread $thread, array $expected): void { self::fake()->assertState($thread, $expected); }
}
