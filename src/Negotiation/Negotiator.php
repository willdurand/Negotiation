<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Negotiator extends AbstractNegotiator
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
            $acceptHeaders[] = new AcceptHeader($acceptPart);
        }

        return $acceptHeaders;
    }

    /**
     * @param AcceptHeader[] $acceptHeaders Sorted by quality
     * @param AcceptHeader[] $priorities    Configured priorities
     *
     * @return AcceptHeader[] Headers matched
     */
    protected static function findMatches(array $acceptHeaders, array $priorities) {
        $matches = array();
        $index = 0;

        foreach ($priorities as $p) {
            foreach ($acceptHeaders as $a) {
                $ab = $a->getBaseType();
                $pb = $p->getBaseType();

                $as = $a->getSubType();
                $ps = $p->getSubType();

                $intersection = array_intersect_assoc($a->getParameters(), $p->getParameters());

                $baseEqual = !strcasecmp($ab, $pb);
                $subEqual = !strcasecmp($as, $ps);

                if (($ab == '*' || $baseEqual) && ($as == '*' || $subEqual) && count($intersection) == count($a->getParameters())) {
                    $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);

                    $matches[] = array($p->getType(), $a->getQuality(), $score, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

}
