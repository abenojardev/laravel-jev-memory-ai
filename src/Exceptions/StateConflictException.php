<?php

namespace Jev\Memory\Exceptions;

use RuntimeException;

final class StateConflictException extends RuntimeException
{
    public function __construct(public readonly int $expected, public readonly int $actual)
    {
        parent::__construct("Thread state version conflict: expected {$expected}, actual {$actual}.");
    }
}
