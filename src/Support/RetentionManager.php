<?php

namespace Jev\Memory\Support;

use Jev\Memory\Models\CompactTurn;
use Jev\Memory\Models\Turn;

final class RetentionManager
{
    public function purge(): array
    {
        $rawDays = config('jev-memory.retention.raw_turn_days');
        $compactDays = config('jev-memory.retention.compact_turn_days');
        $deleted = ['turns' => 0, 'compact_turns' => 0];

        if ($rawDays !== null) {
            $deleted['turns'] = Turn::query()->where('created_at', '<', now()->subDays((int) $rawDays))->delete();
        }
        if ($compactDays !== null) {
            $deleted['compact_turns'] = CompactTurn::query()->where('created_at', '<', now()->subDays((int) $compactDays))->delete();
        }

        return $deleted;
    }
}
