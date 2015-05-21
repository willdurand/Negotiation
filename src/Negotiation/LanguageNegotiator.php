<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
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
    protected function match(AcceptLanguageHeader $acceptLanguageHeader, AcceptLanguageHeader $priority, $index) {
        $ab = $acceptLanguageHeader->getBasePart();
        $pb = $priority->getBasePart();

        $as = $acceptLanguageHeader->getSubPart();
        $ps = $priority->getSubPart();

        $baseEqual = !strcasecmp($ab, $pb);
        $subEqual = !strcasecmp($as, $ps);

        if ($baseEqual && ($as === null || $subEqual)) {
            $score = 10 * $baseEqual + ($as !== null && $subEqual);
            return new Match($priority->getLanguage(), $acceptLanguageHeader->getQuality(), $score, $index);
        }

        return null;
    }

}
