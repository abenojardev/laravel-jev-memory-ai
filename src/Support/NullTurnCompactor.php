<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\TurnCompactor;
use Jev\Memory\Context\CompactionInput;
use Jev\Memory\Context\CompactTurnResult;

final class NullTurnCompactor implements TurnCompactor
{
    public function compact(CompactionInput $input): CompactTurnResult
    {
        return new CompactTurnResult($input->user, $input->assistant, $input->stateDelta);
    }
}
