<?php

namespace Negotiation;

class Negotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept)
    {
        return new Accept($accept);
    }

    /**
     * {@inheritdoc}
     */
    protected function match(AcceptHeader $accept, AcceptHeader $priority, $index)
    {
        if (!$accept instanceof Accept || !$priority instanceof Accept) {
            return null;
        }

        $ab = $accept->getBasePart();
        $pb = $priority->getBasePart();

        $as = $accept->getSubPart();
        $ps = $priority->getSubPart();

        $intersection = array_intersect_assoc($accept->getParameters(), $priority->getParameters());

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual  = !strcasecmp($as, $ps);

        if (($ab === '*' || $baseEqual) && ($as === '*' || $subEqual) && count($intersection) === count($accept->getParameters())) {
            $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);

            return new Match($accept->getQuality(), $score, $index);
        }

        return null;
    }
}
