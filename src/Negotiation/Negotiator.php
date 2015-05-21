<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Negotiator extends AbstractNegotiator
{

    /**
     * @param strint $type
     *
     * @return AcceptHeader
     */
    protected function typeFactory($type)
    {
        return new AcceptHeader($type);
    }

    /**
     * {@inheritdoc}
     */
    protected function match(AcceptHeader $acceptHeader, AcceptHeader $priority) {
        $ab = $acceptHeader->getBasePart();
        $pb = $priority->getBasePart();

        $as = $acceptHeader->getSubPart();
        $ps = $priority->getSubPart();

        $intersection = array_intersect_assoc($acceptHeader->getParameters(), $priority->getParameters());

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual = !strcasecmp($as, $ps);

        if (($ab == '*' || $baseEqual) && ($as == '*' || $subEqual) && count($intersection) == count($acceptHeader->getParameters())) {
            $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);
            $matches[] = new Match($priority->getMediaType(), $acceptHeader->getQuality(), $score, $index);
        }

        return null;
    }

}
