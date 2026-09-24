<?php

namespace Jev\Memory\Contracts;

use Jev\Memory\Models\Memory;
use Jev\Memory\Models\Turn;

interface MemoryRepository
{
    public function remember(string $type, string $content, ?Turn $sourceTurn = null, ?array $structuredData = null, ?float $importance = null): Memory;
}
