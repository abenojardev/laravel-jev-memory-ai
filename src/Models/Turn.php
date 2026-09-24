<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;

class Turn extends Model
{
    protected $table = 'jev_memory_turns';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public function thread() { return $this->belongsTo(Thread::class); }
    public function compactTurn() { return $this->hasOne(CompactTurn::class); }
}
