<?php

namespace Jev\Memory\Context;

use Jev\Memory\Contracts\KnowledgeProvider;
use Jev\Memory\Contracts\MeaningResolver;
use Jev\Memory\Contracts\MemoryRetriever;
use Jev\Memory\Contracts\RetrievalPlanner;
use Jev\Memory\Models\Thread;

final class ContextBuilder
{
    private Thread $thread;

    public function __construct(
        private readonly MeaningResolver $resolver,
        private readonly RetrievalPlanner $planner,
        private readonly MemoryRetriever $memories,
        private readonly KnowledgeProvider $knowledge,
    ) {}

    public function for(Thread $thread): self
    {
        $copy = clone $this;
        $copy->thread = $thread;
        return $copy;
    }

    public function build(string $latestMessage, ?ContextBudget $budget = null): ContextEnvelope
    {
        $state = $this->thread->state()->get();
        $pending = $this->thread->pendingActions()->current();
        $meaning = $this->resolver->resolve(new MeaningContext(
            $this->thread, $latestMessage, $state, $pending?->toArray()
        ));
        $plan = $this->planner->plan($meaning, $state);
        $maxTurns = (int) config('jev-memory.retrieval.max_compact_turns', 12);
        $compact = $this->thread->compactTurns()->latest('id')->limit($maxTurns)->get()->reverse()->values()->map(fn ($turn) => [
            'user' => $turn->compact_content,
            'meaning_type' => $turn->meaning_type,
            'entities' => $turn->entities,
        ])->all();

        return new ContextEnvelope([
            'thread' => [
                'id' => $this->thread->getKey(),
                'workflow' => $this->thread->workflow,
                'stage' => $this->thread->stage,
            ],
            'latest_message' => $latestMessage,
            'resolved_meaning' => $meaning->toArray(),
            'pending_action' => $pending?->only(['name', 'payload', 'expires_at']),
            'compact_history' => $compact,
            'hard_state' => $state,
            'memories' => $this->memories->retrieve($plan, (int) config('jev-memory.retrieval.max_memories', 10)),
            'knowledge' => $this->knowledge->retrieve($plan),
            'tool_context' => [],
            'budget' => $budget?->tokens,
        ]);
    }
}
