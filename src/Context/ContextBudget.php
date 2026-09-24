<?php

namespace Jev\Memory\Context;

final readonly class ContextBudget
{
    private function __construct(public int $tokens) {}

    public static function tokens(int $tokens): self
    {
        if ($tokens < 1) {
            throw new \InvalidArgumentException('Context budget must be positive.');
        }

        return new self($tokens);
    }
}
