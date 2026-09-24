<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;

class CompactTurn extends Model
{
    protected $table = 'jev_memory_compact_turns';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['entities' => 'array', 'created_at' => 'datetime'];
}
