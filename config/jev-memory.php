<?php

return [
    'storage' => 'eloquent',
    'compaction' => ['enabled' => false, 'driver' => 'null'],
    'meaning_resolution' => ['driver' => 'rules', 'confidence_threshold' => 0.80],
    'memory' => ['enabled' => true, 'default_scope' => 'user'],
    'retrieval' => ['max_memories' => 10, 'max_compact_turns' => 12],
    'retention' => ['raw_turn_days' => null, 'compact_turn_days' => null],
];
