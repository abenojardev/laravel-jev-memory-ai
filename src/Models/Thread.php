<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Jev\Memory\Context\ContextBudget;
use Jev\Memory\Context\ContextEnvelope;
use Jev\Memory\Context\ContextBuilder;
use Jev\Memory\Support\PendingActionManager;
use Jev\Memory\Support\StateManager;
use Jev\Memory\Context\CompactionInput;
use Jev\Memory\Contracts\TurnCompactor;

class Thread extends Model
{
    protected $table = 'jev_memory_threads';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['metadata' => 'array', 'closed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $thread): void {
            $thread->id ??= (string) Str::ulid();
            $thread->status ??= 'open';
            $thread->version ??= 0;
        });
    }

    public function turns() { return $this->hasMany(Turn::class); }
    public function compactTurns() { return $this->hasMany(CompactTurn::class); }
    public function stateRecord() { return $this->hasOne(ThreadState::class); }
    public function transitions() { return $this->hasMany(StateTransition::class); }
    public function pendingActionRecords() { return $this->hasMany(PendingAction::class); }
    public function memories() { return $this->hasMany(Memory::class, 'thread_id'); }

    public function state(): StateManager { return new StateManager($this); }
    public function pendingActions(): PendingActionManager { return new PendingActionManager($this); }

    public function ingest(string $message, ?string $idempotencyKey = null, string $role = 'user'): Turn
    {
        return app(\Jev\Memory\Support\TurnIngestor::class)->ingest($this, $message, $idempotencyKey, $role);
    }

    public function appendUserMessage(string $message): Turn { return $this->ingest($message, role: 'user'); }
    public function appendAssistantMessage(string $message): Turn { return $this->ingest($message, role: 'assistant'); }

    public function close(): self
    {
        $this->forceFill(['status' => 'closed', 'closed_at' => now()])->save();
        return $this->refresh();
    }

    public function forget(): void { $this->delete(); }

    public function compact(Turn $turn, ?string $assistant = null, array $stateDelta = []): CompactTurn
    {
        $result = app(TurnCompactor::class)->compact(new CompactionInput(
            $turn->raw_content, $assistant, $this->state()->get(), $stateDelta
        ));

        return $this->compactTurns()->updateOrCreate(
            ['turn_id' => $turn->getKey()],
            [
                'compact_content' => $result->user,
                'meaning_type' => $result->meaningType,
                'entities' => $result->entities,
            ]
        );
    }

    public function context(): ContextBuilder { return app(ContextBuilder::class)->for($this); }
}
