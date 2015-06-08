<?php

namespace Negotiation;

class LanguageNegotiator extends AbstractNegotiator
{

    /**
     * @param strint $type
     *
     * @return AcceptLanguageHeader
     */
    protected function typeFactory($type)
    {
        return new AcceptLanguageHeader($type);
    }

    /**
     * {@inheritdoc}
     */
    protected static function match(Header $acceptLanguageHeader, Header $priority, $index) {
        $ab = $acceptLanguageHeader->getBasePart();
        $pb = $priority->getBasePart();

        $as = $acceptLanguageHeader->getSubPart();
        $ps = $priority->getSubPart();

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual = !strcasecmp($as, $ps);

        if (($ab == '*' || $baseEqual) && ($as === null || $subEqual)) {
            $score = 10 * $baseEqual + $subEqual;

            return new Match($acceptLanguageHeader->getQuality(), $score, $index);
        }

        return null;
    }

}
