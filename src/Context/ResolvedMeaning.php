<?php

namespace Jev\Memory\Context;

final readonly class ResolvedMeaning
{
    public function __construct(
        public string $intent,
        public ?string $subject = null,
        public float $confidence = 1.0,
        public array $entities = [],
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'intent' => $this->intent,
            'subject' => $this->subject,
            'confidence' => $this->confidence,
            'entities' => $this->entities ?: null,
        ], static fn ($value) => $value !== null);
    }
}
