<?php

namespace Jev\Memory\Support;

use Jev\Memory\Models\PendingAction;
use Jev\Memory\Models\Thread;

final class PendingActionManager
{
    public function __construct(private readonly Thread $thread) {}

    public function set(string $name, array $payload = [], mixed $expiresAt = null): PendingAction
    {
        $this->thread->pendingActionRecords()->where('status', 'pending')->update([
            'status' => 'superseded', 'resolved_at' => now(),
        ]);

        $action = $this->thread->pendingActionRecords()->create([
            'name' => $name,
            'payload' => $payload,
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);
        $this->thread->state()->set(['pending_action' => $name], source: 'pending_action');
        return $action;
    }

    public function current(): ?PendingAction
    {
        return $this->thread->pendingActionRecords()
            ->where('status', 'pending')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('id')->first();
    }
}
