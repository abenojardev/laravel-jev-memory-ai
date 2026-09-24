<?php

namespace Jev\Memory\Contracts;

use Jev\Memory\Models\Thread;

interface ThreadRepository
{
    public function create(array $attributes = []): Thread;

    public function find(string|int $id): ?Thread;
}
