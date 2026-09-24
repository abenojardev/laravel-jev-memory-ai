<?php

namespace Jev\Memory\Events;

use Jev\Memory\Models\Thread;

final readonly class ThreadCreated
{
    public function __construct(public string $threadId) {}
}
