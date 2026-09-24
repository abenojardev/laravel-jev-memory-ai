<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\RetrievalPlanner;
use Jev\Memory\Context\ResolvedMeaning;

final class DefaultRetrievalPlanner implements RetrievalPlanner
{
    public function plan(ResolvedMeaning $meaning, array $state): array
    {
        return ['intent' => $meaning->intent, 'subject' => $meaning->subject, 'state' => $state];
    }
}
