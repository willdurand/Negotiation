<?php

namespace Negotiation;

class Negotiator extends AbstractNegotiator
{

    /**
     * @param strint $accept
     *
     * @return Accept
     */
    protected function acceptFactory($accept)
    {
        return new Accept($accept);
    }

    /**
     * {@inheritdoc}
     */
    protected static function match(BaseAccept $accept, BaseAccept $priority, $index) {
        $ab = $accept->getBasePart();
        $pb = $priority->getBasePart();

        $as = $accept->getSubPart();
        $ps = $priority->getSubPart();

        $intersection = array_intersect_assoc($accept->getParameters(), $priority->getParameters());

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual = !strcasecmp($as, $ps);

        if (($ab == '*' || $baseEqual) && ($as == '*' || $subEqual) && count($intersection) == count($accept->getParameters())) {
            $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);

            return new Match($accept->getQuality(), $score, $index);
        }

        return null;
    }

}
