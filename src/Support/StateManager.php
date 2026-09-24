<?php

namespace Jev\Memory\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Jev\Memory\Exceptions\StateConflictException;
use Jev\Memory\Events\StateTransitioned;
use Jev\Memory\Models\Thread;
use Jev\Memory\Models\ThreadState;

final class StateManager
{
    public function __construct(private readonly Thread $thread) {}

    public function get(): array
    {
        $record = $this->record();
        return array_filter(array_merge([
            'workflow' => $record->workflow,
            'stage' => $record->stage,
            'pending_action' => $record->pending_action,
        ], $record->state ?: []), static fn ($value) => $value !== null);
    }

    public function version(): int
    {
        return (int) $this->record()->version;
    }

    public function set(array $state, ?int $expectedVersion = null, string $source = 'application'): ThreadState
    {
        return $this->transition('state.set', $expectedVersion ?? $this->version(), $state, $source);
    }

    public function transition(string $event, int $expectedVersion, array $delta, string $source = 'application'): ThreadState
    {
        return DB::transaction(function () use ($event, $expectedVersion, $delta, $source): ThreadState {
            ThreadState::query()->firstOrCreate(
                ['thread_id' => $this->thread->getKey()],
                ['state' => [], 'version' => 0]
            );
            $record = ThreadState::query()->whereKey($this->thread->getKey())->lockForUpdate()->firstOrFail();
            if ((int) $record->version !== $expectedVersion) {
                throw new StateConflictException($expectedVersion, (int) $record->version);
            }

            $before = $this->recordState($record);
            $after = array_replace($before, $delta);
            $nextVersion = $expectedVersion + 1;
            $record->forceFill([
                'workflow' => $after['workflow'] ?? null,
                'stage' => $after['stage'] ?? null,
                'pending_action' => $after['pending_action'] ?? null,
                'state' => array_diff_key($after, array_flip(['workflow', 'stage', 'pending_action'])),
                'version' => $nextVersion,
                'updated_at' => now(),
            ])->save();

            $this->thread->forceFill([
                'workflow' => $after['workflow'] ?? null,
                'stage' => $after['stage'] ?? null,
                'version' => $nextVersion,
            ])->save();

            $this->thread->transitions()->create([
                'from_version' => $expectedVersion,
                'to_version' => $nextVersion,
                'event' => $event,
                'before' => $before,
                'delta' => $delta,
                'after' => $after,
                'source' => $source,
            ]);

            Event::dispatch(new StateTransitioned(
                (string) $this->thread->getKey(), $event, $expectedVersion, $nextVersion
            ));

            return $record->fresh();
        });
    }

    private function record(): ThreadState
    {
        return $this->thread->stateRecord()->firstOrCreate(['thread_id' => $this->thread->getKey()], [
            'state' => [], 'version' => 0,
        ]);
    }

    private function recordState(ThreadState $record): array
    {
        return array_filter(array_merge([
            'workflow' => $record->workflow,
            'stage' => $record->stage,
            'pending_action' => $record->pending_action,
        ], $record->state ?: []), static fn ($value) => $value !== null);
    }
}
