# Jev Memory for Laravel

Conversation context, thread state, and durable memory for Laravel applications using [Jev](https://github.com/abenojardev/laravel-jev-ai).

Jev Memory gives an application a reliable context layer between a conversation and an AI decision. It preserves raw turns, records explicit workflow state, resolves short follow-ups such as `Yes` against that state, and retrieves only the context relevant to the current turn.

> **Status:** V1 thread-core implementation is complete at the package level. Run the Testbench suite in an environment with Composer dependencies before tagging a release. Jev-backed resolution, advanced retrieval, and vector search remain optional extension points.

## Why Jev Memory?

The latest message is often not meaningful by itself:

```text
Assistant: Should I submit the enquiry?
User: Yes
```

The application needs the thread state to interpret that answer safely:

```text
workflow: enquiry
stage: awaiting_confirmation
pending_action: submit_enquiry
```

Jev Memory keeps these concerns separate:

```text
Context
├── Thread state       exact workflow and application state
├── Compact history    efficient semantic turn history
├── Long-term memory   durable facts and preferences
└── Knowledge          rules, policies, prompts, and skills
```

Thread state is not long-term memory, knowledge is not user memory, and neither Jev nor a model becomes the source of truth. Laravel validates and persists authoritative state.

## Design goals

- Explicit, versioned, auditable state transitions
- Raw conversation turns preserved as source records
- First-class pending actions for confirmation workflows
- Idempotent turn ingestion and duplicate protection
- Deterministic context priority and size budgeting
- Pluggable meaning resolution, compaction, retrieval, and storage
- Eloquent, queues, events, dependency injection, and testing support
- Provenance and lifecycle controls for durable memory
- Optional Jev integration without coupling the domain model to the Jev SDK

## Non-goals

Jev Memory does not replace:

- Your business database or application state
- Business action execution
- A complete RAG or knowledge-management system
- Laravel's authorization, validation, or transaction boundaries
- The Jev PHP adapter

## Requirements

- PHP 8.2+
- Laravel 10, 11, or later
- A supported Laravel database driver
- [Jev](https://github.com/abenojardev/laravel-jev-ai) when using the Jev-backed adapters

The exact supported PHP and Laravel versions should be confirmed when the first release is tagged.

## Installation

Install the package with Composer:

```bash
composer require abenojardev/jev-memory-laravel
```

Laravel package discovery registers the service provider automatically. If discovery is disabled, register the provider manually:

```php
// bootstrap/providers.php (Laravel 11+)
return [
    App\Providers\AppServiceProvider::class,
    Jev\Memory\Laravel\JevMemoryServiceProvider::class,
];
```

Publish the configuration and migrations, then migrate:

```bash
php artisan vendor:publish --tag=jev-memory-config
php artisan vendor:publish --tag=jev-memory-migrations
php artisan migrate
```

## Configuration

The published `config/jev-memory.php` file controls storage, adapters, retrieval, and retention:

```php
return [
    'storage' => 'eloquent',

    'compaction' => [
        'enabled' => true,
        'driver' => 'jev',
    ],

    'meaning_resolution' => [
        'driver' => 'jev',
        'confidence_threshold' => 0.80,
    ],

    'memory' => [
        'enabled' => true,
        'default_scope' => 'user',
    ],

    'retrieval' => [
        'lexical' => true,
        'vector' => false,
        'max_memories' => 10,
        'max_compact_turns' => 12,
    ],

    'retention' => [
        'raw_turn_days' => null,
        'compact_turn_days' => null,
    ],
];
```

Vector search, Redis, and a dedicated vector database are optional. V1 should work with the normal Laravel database and does not require them.

## Quick start

Create a thread and record its workflow state:

```php
use Jev\Memory\Facades\JevMemory;
use Jev\Memory\Context\ContextBudget;

$thread = JevMemory::threads()->create([
    'user_key' => (string) $user->id,
    'workflow' => 'enquiry',
    'stage' => 'awaiting_confirmation',
]);

$thread->pendingActions()->set(
    name: 'submit_enquiry',
    payload: ['enquiry_id' => $enquiry->id],
    expiresAt: now()->addMinutes(30),
);
```

Ingest the next message idempotently:

```php
$turn = $thread->ingest(
    message: $request->string('message')->toString(),
    idempotencyKey: $providerMessageId,
);
```

Build the context passed to Jev or another decision layer:

```php
$context = $thread->context()->build(
    latestMessage: $turn->raw_content,
    budget: ContextBudget::tokens(4000),
);

$decision = $jev->respond($context->toArray());
```

The resulting envelope is predictable and separates authoritative data from retrieved context:

```json
{
  "thread": {
    "id": "thr_123",
    "workflow": "enquiry",
    "stage": "awaiting_confirmation"
  },
  "latest_message": "Yes",
  "resolved_meaning": {
    "intent": "confirm_pending_action",
    "subject": "submit_enquiry"
  },
  "pending_action": {
    "name": "submit_enquiry"
  },
  "compact_history": [],
  "hard_state": {},
  "memories": [],
  "knowledge": [],
  "tool_context": []
}
```

## State transitions

State changes are explicit, versioned, and auditable:

```php
$thread->state()->transition(
    event: 'enquiry.confirmed',
    expectedVersion: $thread->state()->version,
    delta: [
        'stage' => 'confirmed',
        'pending_action' => null,
    ],
);
```

Concurrent updates must use optimistic locking. If another worker has already changed the state, the transition throws `StateConflictException`; the caller can reload the thread and decide whether a retry is safe.

Business actions remain application-owned:

```php
// Validate authorization and business rules before executing this action.
$enquiryService->submit($pending->payload['enquiry_id']);
```

Do not allow model-generated text or a compact turn to execute a business action directly.

## Pending actions

Pending actions make confirmation turns explicit instead of forcing the system to infer critical intent from prose:

```php
$pending = $thread->pendingActions()->current();

$pending->confirm();
$pending->reject();
$pending->cancel();
```

Actions may expire or be superseded. An expired action must not be treated as an active confirmation target.

## Long-term memory

Long-term memory is separate from thread state and must have provenance:

```php
JevMemory::memories()
    ->forUser($user->id)
    ->remember(
        type: 'preference',
        content: 'Prefers acoustic acts for corporate events.',
        sourceTurn: $turn,
    );
```

Memory supports scope, expiry, supersession, deletion, and retrieval. Typical scopes include `thread`, `user`, `account`, `event`, and application-defined scopes.

Authoritative application state always wins over memory. The default priority is:

1. Application and hard state
2. Current thread state
3. Current tool results
4. Retrieved knowledge and policies
5. Long-term memory
6. Compact history
7. Raw historical turns

## Extending the package

The core depends on contracts so an application can replace individual stages:

```php
interface MeaningResolver
{
    public function resolve(MeaningContext $context): ResolvedMeaning;
}

interface TurnCompactor
{
    public function compact(CompactionInput $input): CompactTurnResult;
}
```

Other extension points include:

- `ThreadRepository`
- `MemoryRepository`
- `RetrievalPlanner`
- `MemoryRetriever`
- `KnowledgeProvider`
- `RelevanceGate`

The official Jev adapters implement meaning resolution, compaction, retrieval planning, and relevance gating. Deterministic application rules can implement the same contracts without an LLM.

## Recommended request flow

1. Idempotently append the raw user turn.
2. Load authoritative application state and current thread state.
3. Resolve the latest message against that state.
4. Retrieve mandatory knowledge, memories, and relevant history.
5. Apply relevance gates and the context budget.
6. Build the `ContextEnvelope` and pass it to Jev or the application decision layer.
7. Validate and execute business actions in application code.
8. Persist the explicit state delta and raw assistant turn.
9. Compact the completed turn without deleting the raw records.
10. Evaluate durable-memory candidates and commit the audit trail.

## Events and observability

The package can dispatch events such as:

- `ThreadCreated`
- `TurnAdded`
- `TurnCompacted`
- `MeaningResolved`
- `StateTransitioned`
- `PendingActionChanged`
- `MemoryWritten`
- `MemorySuperseded`
- `MemoryEvicted`
- `ContextBuilt`
- `RetrievalCompleted`

Events should contain identifiers and safe metadata rather than full prompt payloads by default.

Optional diagnostics can record resolver and retrieval latency, candidate counts, context size, memory writes, state conflicts, and compaction failures. A debug representation should explain why each context item was included, for example:

```json
{
  "memory_id": 42,
  "included_because": [
    "scope:user:123",
    "topic:enquiry",
    "recent",
    "relevance_gate:true"
  ]
}
```

## Privacy and retention

Conversation content may contain sensitive information. Applications should provide and test APIs for:

- Deleting a thread
- Deleting user-scoped memory
- Retention windows for raw and compact turns
- Redaction hooks
- Data export
- Disabling long-term memory per user or thread

Avoid logging raw conversation content unless it is explicitly required and protected.

Use the lifecycle APIs from scheduled jobs or application actions:

```php
$thread->forget();

JevMemory::memories()->forUser($user->id)->deleteForScope();
JevMemory::memories()->expire();
JevMemory::retention()->purge();
```

`purge()` uses `retention.raw_turn_days` and `retention.compact_turn_days`. Leave either value `null` to retain that record type indefinitely.

## Testing

The package should ship in-memory and fake implementations for repositories, resolvers, compactors, retrievers, and knowledge providers. A typical scenario test looks like this:

```php
JevMemory::fake();

$thread = JevMemory::threads()->create();
$thread->state()->set([
    'pending_action' => 'submit_enquiry',
]);

$thread->appendUserMessage('Yes');

JevMemory::assertState($thread, [
    'pending_action' => 'submit_enquiry',
]);
```

The minimum regression scenarios are:

- `Yes` confirms the exact active pending action.
- A request to change the venue does not confirm that action.
- Current application data wins over stale memory.
- Two workers cannot process the same confirmation twice.
- Expired actions are not confirmable.
- Memories from another event or scope are rejected.
- Raw turns remain available after compaction.

## Integration with Jev

The intended relationship is:

```text
jev-php
   ↓
JevMeaningResolver
JevTurnCompactor
JevRetrievalPlanner
JevRelevanceGate
   ↓
jev-memory-laravel
```

Jev can help interpret and rank context, but Laravel owns state validation, persistence, authorization, transactions, and business actions.

## Roadmap

### V1 — Thread core

- Threads, raw turns, compact turns, and state
- Pending actions and audited transitions
- Eloquent storage with optimistic locking
- Idempotent ingestion
- Context envelope and fake testing support

### V1.1 — Jev intelligence

- Jev meaning resolver and turn compactor
- Retrieval planner and relevance gate
- Context diagnostics

### V1.2 — Long-term memory

- Durable memory scopes and provenance
- Supersession, expiry, retrieval, and eviction gates

### V2 — Advanced retrieval

- Hybrid lexical and vector retrieval
- Entity and temporal relationships
- Configurable retrieval budgets and adaptive paths

Vector and graph complexity should not block the thread core.

## License

Add the repository's chosen license here before the first public release.
