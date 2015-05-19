<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class LanguageNegotiator extends AbstractNegotiator
{

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return AcceptHeader[]
     */
    private static function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($acceptParts as $acceptPart) {
            $acceptHeaders[] = new LanguageAcceptHeader($acceptPart);
        }

        return $acceptHeaders;
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
                    $matches[] = array($p->getType(), $a->getQuality(), $score, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

}
