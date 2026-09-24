<?php

namespace Jev\Memory\Contracts;

use Jev\Memory\Context\MeaningContext;
use Jev\Memory\Context\ResolvedMeaning;

interface MeaningResolver
{
    public function resolve(MeaningContext $context): ResolvedMeaning;
}
