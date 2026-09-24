<?php

namespace Jev\Memory\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jev\Memory\Context\ContextBudget;
use Jev\Memory\Exceptions\StateConflictException;
use Jev\Memory\Facades\JevMemory;
use Jev\Memory\Tests\TestCase;

final class ThreadCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_context_uses_the_active_pending_action(): void
    {
        $thread = JevMemory::threads()->create(['user_key' => 'user-1', 'workflow' => 'enquiry']);
        $thread->pendingActions()->set('submit_enquiry', ['enquiry_id' => 123]);
        $thread->appendAssistantMessage('Should I submit the enquiry?');
        $thread->appendUserMessage('Yes');

        $context = $thread->context()->build('Yes', ContextBudget::tokens(4000))->toArray();

        $this->assertSame('confirm_pending_action', $context['resolved_meaning']['intent']);
        $this->assertSame('submit_enquiry', $context['resolved_meaning']['subject']);
        $this->assertCount(2, $thread->fresh()->turns);
    }

    public function test_duplicate_idempotency_key_returns_the_original_turn(): void
    {
        $thread = JevMemory::threads()->create();
        $first = $thread->ingest('Yes', 'provider-message-1');
        $second = $thread->ingest('Yes', 'provider-message-1');

        $this->assertTrue($first->is($second));
        $this->assertSame(1, $thread->fresh()->turns()->count());
    }

    public function test_state_transition_requires_the_expected_version(): void
    {
        $thread = JevMemory::threads()->create();
        $version = $thread->state()->version();
        $thread->state()->transition('workflow.started', $version, ['stage' => 'started']);

        $this->expectException(StateConflictException::class);
        $thread->state()->transition('workflow.stale', $version, ['stage' => 'stale']);
    }

    public function test_compaction_adds_a_compact_record_without_deleting_the_raw_turn(): void
    {
        $thread = JevMemory::threads()->create();
        $turn = $thread->appendUserMessage('The venue is T\'boli.');

        $compact = $thread->compact($turn);

        $this->assertSame($turn->raw_content, $compact->compact_content);
        $this->assertSame($turn->raw_content, $thread->fresh()->turns()->first()->raw_content);
        $this->assertSame(1, $thread->fresh()->compactTurns()->count());
    }

    public function test_fake_assertion_and_scoped_memory_lifecycle_work(): void
    {
        $thread = JevMemory::threads()->create();
        $thread->state()->set(['stage' => 'collecting']);
        JevMemory::fake()->assertState($thread, ['stage' => 'collecting']);

        $memory = JevMemory::memories()->forUser('user-1')->remember('preference', 'Acoustic acts.');
        $this->assertCount(1, JevMemory::memories()->forUser('user-1')->active());
        JevMemory::memories()->forUser('user-1')->supersede($memory);
        $this->assertSame('superseded', $memory->refresh()->status);
        $this->assertSame(1, JevMemory::memories()->forUser('user-1')->deleteForScope());
    }
}
