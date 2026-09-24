<?php

namespace Jev\Memory\Support;

use Jev\Memory\Models\Thread;

final class JevMemoryFake
{
    public function assertState(Thread $thread, array $expected): void
    {
        $actual = $thread->fresh()->state()->get();
        foreach ($expected as $key => $value) {
            if (($actual[$key] ?? null) !== $value) {
                throw new \RuntimeException("Expected thread state [{$key}] to be [" . var_export($value, true) . '], got [' . var_export($actual[$key] ?? null, true) . '].');
            }
        }
    }
}
