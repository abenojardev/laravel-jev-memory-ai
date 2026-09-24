<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\MemoryRetriever;

final class NullMemoryRetriever implements MemoryRetriever
{
    public function retrieve(array $plan, int $limit = 10): array { return []; }
}
