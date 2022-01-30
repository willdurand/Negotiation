<?php

namespace Negotiation;

class LanguageNegotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept)
    {
        return new AcceptLanguage($accept);
    }

    /**
     * {@inheritdoc}
     */
    protected function match(AcceptHeader $acceptLanguage, AcceptHeader $priority, $index)
    {
        if (!$acceptLanguage instanceof AcceptLanguage || !$priority instanceof AcceptLanguage) {
            return null;
        }

        $ab = $acceptLanguage->getBasePart();
        $pb = $priority->getBasePart();

        $as = $acceptLanguage->getSubPart();
        $ps = $priority->getSubPart();

        $baseEqual = !strcasecmp((string)$ab, (string)$pb);
        $subEqual  = !strcasecmp((string)$as, (string)$ps);

        if (($ab == '*' || $baseEqual) && ($as === null || $subEqual || null === $ps)) {
            $score = 10 * $baseEqual + $subEqual;

            return new AcceptMatch($acceptLanguage->getQuality() * $priority->getQuality(), $score, $index);
        }

        return null;
    }
}
