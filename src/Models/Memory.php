<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;

class Memory extends Model
{
    protected $table = 'jev_memory_memories';
    protected $guarded = [];
    protected $casts = [
        'structured_data' => 'array', 'importance' => 'float',
        'valid_from' => 'datetime', 'valid_until' => 'datetime',
    ];
}
