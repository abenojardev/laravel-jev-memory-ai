<?php

namespace Jev\Memory\Contracts;

interface KnowledgeProvider
{
    public function retrieve(array $plan): array;
}
