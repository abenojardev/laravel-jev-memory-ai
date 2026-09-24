<?php

namespace Jev\Memory\Models;

use Illuminate\Database\Eloquent\Model;

class PendingAction extends Model
{
    protected $table = 'jev_memory_pending_actions';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['payload' => 'array', 'expires_at' => 'datetime', 'created_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function confirm(): self { return $this->resolve('confirmed'); }
    public function reject(): self { return $this->resolve('rejected'); }
    public function cancel(): self { return $this->resolve('cancelled'); }

    private function resolve(string $status): self
    {
        if ($this->status === 'pending') {
            $this->forceFill(['status' => $status, 'resolved_at' => now()])->save();
        }

        return $this->refresh();
    }
}
