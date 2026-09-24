<?php

namespace Jev\Memory\Events;

final readonly class TurnAdded
{
    public function __construct(public int|string $turnId, public string $threadId) {}
}
