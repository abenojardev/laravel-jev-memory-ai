<?php

namespace Jev\Memory\Context;

final readonly class CompactionInput
{
    public function __construct(
        public string $user,
        public ?string $assistant,
        public array $previousState = [],
        public array $stateDelta = [],
    ) {}
}
