<?php

namespace Jev\Memory\Contracts;

use Jev\Memory\Context\ResolvedMeaning;

interface RetrievalPlanner
{
    public function plan(ResolvedMeaning $meaning, array $state): array;
}
