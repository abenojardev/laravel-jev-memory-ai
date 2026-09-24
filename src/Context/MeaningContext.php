<?php

namespace Jev\Memory\Context;

use Jev\Memory\Models\Thread;

final readonly class MeaningContext
{
    public function __construct(
        public Thread $thread,
        public string $latestMessage,
        public array $state,
        public ?array $pendingAction = null,
    ) {}
}
