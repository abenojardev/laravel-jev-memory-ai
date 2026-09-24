<?php

namespace Jev\Memory\Contracts;

interface RelevanceGate
{
    public function accept(mixed $item, array $context): bool;
}
