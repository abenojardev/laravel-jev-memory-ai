<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\KnowledgeProvider;

final class NullKnowledgeProvider implements KnowledgeProvider
{
    public function retrieve(array $plan): array { return []; }
}
