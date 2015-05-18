<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class LanguageNegotiator extends Negotiator
{

    /** 
     * {@inheritDoc}
     */
    public function getBest($header, array $priorities = array()) {
        $best = parent::getBest($header, $priorities);

        if ($best === null)
            return $best;

        return new AcceptHeader($best->getValue(), $best->getQuality());
    }

    /**
     * @param AcceptHeader[] $languageHeaders Sorted by quality
     * @param AcceptHeader[] $priorities    Configured priorities
     *
     * @return AcceptHeader[] Headers matched
     */
    protected static function findMatches(array $languageHeaders, array $priorities) {
        $matches = array();
        $index = 0;

        foreach ($priorities as $p) {
            foreach ($languageHeaders as $a) {
                $ab = $a->getBaseType();
                $pb = $p->getBaseType();

                $as = $a->getSubType();
                $ps = $p->getSubType();

                $baseEqual = !strcasecmp($ab, $pb);
                $subEqual = !strcasecmp($as, $ps);

                if ($baseEqual && ($as === null || $subEqual)) {
                    $score = 10 * $baseEqual + ($as !== null && $subEqual);
                    $matches[] = array($p, $a->getQuality(), $score, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return AcceptHeader[]
     */
    protected static function headerFactory($header) {
        return new AcceptLanguageHeader($header);
    }

}
