<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class LanguageNegotiator extends AbstractNegotiator
{

    /**
     * @param string $header A string that contains an `Accept-Language` header.
     *
     * @return AcceptLanguageHeader[]
     */
    private static function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if ($acceptParts) {
            throw new Exception('failed to parse Accept-Languge header');
        }

        foreach ($acceptParts as $acceptPart) {
            $acceptHeaders[] = new LanguageAcceptHeader($acceptPart);
        }

        return $acceptHeaders;
    }

    /**
     * @param array $priorities list of server priorities
     *
     * @return AcceptLanguageHeader[]
     */
    private static function parsePriorities($priorities)
    {
        return array_map(function($p) { return new AcceptLanguageHeader($p); }, $priorities);
    }

    /**
     * @param AcceptLanguageHeader[] $languageHeaders Sorted by quality
     * @param Priority[] $priorities    Configured priorities
     *
     * @return Match[] Headers matched
     */
    protected static function findMatches(array $languageHeaders, array $priorities) {
        $matches = array();
        $index = 0;

        foreach ($priorities as $p) {
            foreach ($languageHeaders as $a) {
                $ab = $a->getBasePart();
                $pb = $p->getBasePart();

                $as = $a->getSubPart();
                $ps = $p->getSubPart();

                $baseEqual = !strcasecmp($ab, $pb);
                $subEqual = !strcasecmp($as, $ps);

                if ($baseEqual && ($as === null || $subEqual)) {
                    $score = 10 * $baseEqual + ($as !== null && $subEqual);
                    $matches[] = new Match($p->getPart(), $a->getQuality(), $score, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

}
