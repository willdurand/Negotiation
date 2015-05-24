<?php

namespace Negotiation;

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
    protected function match(Header $acceptHeader, Header $priority, $index) {
        $ab = $acceptHeader->getBasePart();
        $pb = $priority->getBasePart();

        $as = $acceptHeader->getSubPart();
        $ps = $priority->getSubPart();

        $intersection = array_intersect_assoc($acceptHeader->getParameters(), $priority->getParameters());

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual = !strcasecmp($as, $ps);

//var_dump($acceptHeader->getType(), $priority->getType(), $baseEqual, $subEqual);

        if (($ab == '*' || $baseEqual) && ($as == '*' || $subEqual) && count($intersection) == count($acceptHeader->getParameters())) {
            $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);
            return new Match($priority->getType(), $acceptHeader->getQuality(), $score, $index);
        }

        return null;
    }

}
