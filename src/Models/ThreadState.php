<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadState extends Model
{
    protected $table = 'jev_memory_thread_states';
    protected $guarded = [];
    protected $casts = ['state' => 'array'];
    public $timestamps = false;

    public function thread() { return $this->belongsTo(Thread::class); }
}
