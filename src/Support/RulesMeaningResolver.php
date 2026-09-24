<?php

namespace Jev\Memory\Support;

use Jev\Memory\Contracts\MeaningResolver;
use Jev\Memory\Context\MeaningContext;
use Jev\Memory\Context\ResolvedMeaning;

final class RulesMeaningResolver implements MeaningResolver
{
    public function resolve(MeaningContext $context): ResolvedMeaning
    {
        $message = mb_strtolower(trim($context->latestMessage));
        if ($context->pendingAction && preg_match('/^(yes|y|yeah|yep|sure|confirm|confirmed|okay|ok)$/i', $message)) {
            return new ResolvedMeaning('confirm_pending_action', $context->pendingAction['name'], 1.0);
        }
        if ($context->pendingAction && preg_match('/^(no|n|nope|cancel|reject|stop)$/i', $message)) {
            return new ResolvedMeaning('reject_pending_action', $context->pendingAction['name'], 1.0);
        }
        return new ResolvedMeaning('message', null, 1.0);
    }
}
