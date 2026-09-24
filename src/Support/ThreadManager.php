<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\ThreadRepository;
use Jev\Memory\Models\Thread;
use Jev\Memory\Models\ThreadState;
use Illuminate\Support\Facades\Event;
use Jev\Memory\Events\ThreadCreated;

final class ThreadManager implements ThreadRepository
{
    public function create(array $attributes = []): Thread
    {
        $thread = Thread::query()->create($attributes);
        ThreadState::query()->create(['thread_id' => $thread->getKey(), 'state' => [], 'version' => 0]);
        $thread->state()->set([], source: 'thread.created');
        Event::dispatch(new ThreadCreated((string) $thread->getKey()));
        return $thread->fresh();
    }

    public function find(string|int $id): ?Thread
    {
        return Thread::query()->find($id);
    }
}
