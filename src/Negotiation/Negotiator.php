<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Negotiator extends AbstractNegotiator
{

    /**
     * @param string $header A string that contains an `Accept` header.
     *
     * @return AcceptHeader[]
     */
    private static function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

# TODO exception for empty?
        foreach ($acceptParts as $acceptPart) {
            $acceptHeaders[] = new AcceptHeader($acceptPart);
        }

        return $acceptHeaders;
    }

    /**
     * @param array $priorities list of server priorities
     *
     * @return AcceptHeader[]
     */
    private static function parsePriorities($priorities)
    {
        return array_map(function($p) { return new AcceptHeader($p); }, $priorities);
    }

    /**
     * @param AcceptHeader[] $acceptHeaders Sorted by quality
     * @param Priority[]     $priorities    Configured priorities
     *
     * @return Match[] Headers matched
     */
    protected static function findMatches(array $acceptHeaders, array $priorities) {
        $matches = array();
        $index = 0;

        foreach ($priorities as $p) {
            foreach ($acceptHeaders as $a) {
                $ab = $a->getBasePart();
                $pb = $p->getBasePart();

                $as = $a->getSubPart();
                $ps = $p->getSubPart();

                $intersection = array_intersect_assoc($a->getParameters(), $p->getParameters());

                $baseEqual = !strcasecmp($ab, $pb);
                $subEqual = !strcasecmp($as, $ps);

                if (($ab == '*' || $baseEqual) && ($as == '*' || $subEqual) && count($intersection) == count($a->getParameters())) {
                    $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);

                    $matches[] = new Match($p->getPart(), $a->getQuality(), $score, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

}
