<?php

namespace Negotiation;

class LanguageNegotiator extends AbstractNegotiator
{

    /**
     * @param strint $accept
     *
     * @return AcceptLanguage
     */
    protected function acceptFactory($accept)
    {
        return new AcceptLanguage($accept);
    }

    /**
     * {@inheritdoc}
     */
    protected static function match(BaseAccept $acceptLanguage, BaseAccept $priority, $index) {
        $ab = $acceptLanguage->getBasePart();
        $pb = $priority->getBasePart();

        $as = $acceptLanguage->getSubPart();
        $ps = $priority->getSubPart();

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual = !strcasecmp($as, $ps);

        if (($ab == '*' || $baseEqual) && ($as === null || $subEqual)) {
            $score = 10 * $baseEqual + $subEqual;

            return new Match($acceptLanguage->getQuality(), $score, $index);
        }

        return null;
    }

}
