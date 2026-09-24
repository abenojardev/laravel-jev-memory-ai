<?php

namespace Jev\Memory\Contracts;

use Jev\Memory\Context\CompactionInput;
use Jev\Memory\Context\CompactTurnResult;

interface TurnCompactor
{
    public function compact(CompactionInput $input): CompactTurnResult;
}
