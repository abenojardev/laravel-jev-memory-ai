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

    public function active(int $limit = 10): array
    {
        return Memory::query()
            ->where('status', 'active')
            ->when($this->scopeType !== null, fn ($query) => $query->where('scope_type', $this->scopeType)->where('scope_key', $this->scopeKey))
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhere('valid_until', '>', now()))
            ->latest('importance')->latest('updated_at')->limit($limit)->get()->all();
    }

    public function supersede(Memory $memory, ?Memory $replacement = null): Memory
    {
        $memory->forceFill([
            'status' => 'superseded',
            'superseded_by' => $replacement?->getKey(),
        ])->save();
        return $memory->refresh();
    }

    public function expire(): int
    {
        return Memory::query()->where('status', 'active')->whereNotNull('valid_until')->where('valid_until', '<=', now())->update(['status' => 'expired', 'updated_at' => now()]);
    }

    public function deleteForScope(): int
    {
        if ($this->scopeType === null || $this->scopeKey === null) {
            throw new \LogicException('A memory scope is required before deletion.');
        }

        return Memory::query()->where('scope_type', $this->scopeType)->where('scope_key', $this->scopeKey)->delete();
    }
}
