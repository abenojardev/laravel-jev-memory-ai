<?php

namespace Jev\Memory\Context;

use JsonSerializable;

final readonly class ContextEnvelope implements JsonSerializable
{
    public function __construct(private array $payload) {}

    public function toArray(): array
    {
        return $this->payload;
    }

    public function jsonSerialize(): array
    {
        return $this->payload;
    }
}
