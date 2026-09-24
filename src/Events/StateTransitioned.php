<?php

namespace Jev\Memory\Events;

final readonly class StateTransitioned
{
    public function __construct(
        public string $threadId,
        public string $event,
        public int $fromVersion,
        public int $toVersion,
    ) {}
}
