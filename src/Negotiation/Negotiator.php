<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Negotiator implements NegotiatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBest($header, array $priorities = array())
    {
        $parts = $this->parseHeader($header);

        if (empty($parts)) {
            return null;
        } elseif (empty($priorities)) {
            return reset($parts);
        }

        $ps = array();
        foreach ($priorities as $p) {
            $ps[] = $this->headerFactory($p);
        }

        $matches = $this->findMatches($parts, $ps);

        usort($matches, array($this, 'compare'));

        if (count($matches)) {
            return $matches[0][0];
        }

        return null;
    }

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return AcceptHeader[]
     */
    protected static function headerFactory($header) {
        return new AcceptHeader($header);
    }

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return AcceptHeader[]
     */
    private static function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
            $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        foreach ($acceptParts as $acceptPart) {
            $acceptHeaders[] = self::headerFactory($acceptPart);
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

                if (($ab == '*' || $baseEqual) && ($as === null || $as == '*' || $subEqual) && count($intersection) == count($a->getParameters())) {
                    $score = 100 * $baseEqual + 10 * ($as !== null && $subEqual) + count($intersection);

                    $matches[] = array($p, $a->getQuality(), $score, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

    /**
     * @param array $a array(accept header, number of matched params)
     * @param array $b array(accept header, number of matched params)
     *
     * @return int
     */
    private static function compare(array $a, array $b) {
        # TODO should we order first according to the more specific match or by the higher q value?
        # TODO unit tests from rfc https://tools.ietf.org/html/rfc7231#section-5.3.2. call usort() in test case.

        list($acceptHeaderA, $matchedQualityA, $scoreA, $indexA) = $a;
        list($acceptHeaderB, $matchedQualityB, $scoreB, $indexB) = $b;

        if ($matchedQualityA > $matchedQualityB) {
            return -1;
        } else if ($matchedQualityA < $matchedQualityB) {
            return 1;
        }

        # priority to more specific match
        if ($acceptHeaderA->getMediaType() == $acceptHeaderB->getMediaType()) {
            if ($scoreA < $scoreB) {
                return 1;
            } else if ($scoreA > $scoreB) {
                return -1;
            }
        }

        if ($indexA < $indexB) {
            return 1;
        } else if ($indexA > $indexB) {
            return -1;
        }

        return 0; # should not occur.
    }

}
