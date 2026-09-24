<?php

namespace Jev\Memory\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Jev\Memory\Events\TurnAdded;
use Jev\Memory\Models\Thread;
use Jev\Memory\Models\Turn;

final class TurnIngestor
{
    public function ingest(Thread $thread, string $message, ?string $idempotencyKey, string $role): Turn
    {
        return DB::transaction(function () use ($thread, $message, $idempotencyKey, $role): Turn {
            if ($idempotencyKey !== null) {
                $existing = $thread->turns()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $sequence = ((int) $thread->turns()->lockForUpdate()->max('sequence')) + 1;
            $turn = $thread->turns()->create([
                'sequence' => $sequence,
                'role' => $role,
                'raw_content' => $message,
                'idempotency_key' => $idempotencyKey,
                'metadata' => [],
            ]);
            Event::dispatch(new TurnAdded($turn->getKey(), (string) $thread->getKey()));
            return $turn;
        });
    }
}
