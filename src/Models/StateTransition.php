<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;

class StateTransition extends Model
{
    protected $table = 'jev_memory_state_transitions';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['before' => 'array', 'delta' => 'array', 'after' => 'array', 'created_at' => 'datetime'];
}
