<?php

namespace Jev\Memory\Context;

final readonly class CompactTurnResult
{
    public function __construct(
        public string $user,
        public ?string $assistant = null,
        public array $stateDelta = [],
        public ?string $meaningType = null,
        public array $entities = [],
    ) {}
}
