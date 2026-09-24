<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\MemoryRepository;
use Jev\Memory\Models\Memory;
use Jev\Memory\Models\Turn;

final class MemoryManager implements MemoryRepository
{
    private ?string $scopeType = null;
    private ?string $scopeKey = null;

    public function forUser(string|int $key): self
    {
        $copy = clone $this;
        $copy->scopeType = 'user';
        $copy->scopeKey = (string) $key;
        return $copy;
    }

    public function forScope(string $type, string|int $key): self
    {
        $copy = clone $this;
        $copy->scopeType = $type;
        $copy->scopeKey = (string) $key;
        return $copy;
    }

    public function remember(string $type, string $content, ?Turn $sourceTurn = null, ?array $structuredData = null, ?float $importance = null): Memory
    {
        return Memory::query()->create([
            'scope_type' => $this->scopeType ?? config('jev-memory.memory.default_scope', 'user'),
            'scope_key' => $this->scopeKey ?? 'global',
            'type' => $type,
            'content' => $content,
            'structured_data' => $structuredData,
            'importance' => $importance,
            'source_turn_id' => $sourceTurn?->getKey(),
            'thread_id' => $sourceTurn?->thread_id,
            'status' => 'active',
        ]);
    }

    public function create(array $attributes): Memory { return Memory::query()->create($attributes); }
}
