<?php

namespace Jev\Memory\Contracts;

interface MemoryRetriever
{
    public function retrieve(array $plan, int $limit = 10): array;
}
