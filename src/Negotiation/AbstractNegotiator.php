<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractNegotiator
{
    /**
     * @param string $header     A string containing an `Accept|Accept-*` header.
     * @param array  $priorities A set of priorities.
     *
     * @return AbstractHeader
     */
    public function getBest($header, array $priorities)
    {
        $parts = $this->parseHeader($header);

        if (empty($parts)) {
            return null;
        }

        if (!$priorities) {
            throw new Exception('no priorities given');
        }

        $ps = array();
        foreach ($priorities as $p) {
            $ps[] = new PriorityHeader($p);
        }

        $matches = $this->findMatches($parts, $ps);

        usort($matches, array($this, 'compare'));

        if (count($matches)) {
            $index = $matches[0][3];
            return $priorities[$index];
        }

        return null;
    }

    /**
     * @param array $a array(accept header, number of matched params)
     * @param array $b array(accept header, number of matched params)
     *
     * @return int
     */
    private static function compare(array $a, array $b) {
        list($typeA, $qualityA, $scoreA, $indexA) = $a;
        list($typeB, $qualityB, $scoreB, $indexB) = $b;

        if ($qualityA > $qualityB) {
            return -1;
        } else if ($qualityA < $qualityB) {
            return 1;
        }

        # priority goes to to more specific match
        if ($typeA == $typeB) {
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

        throw new Exception('failed to compare priorities.');
    }

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return AbstractHeader[]
     */
    abstract protected static function parseHeader($header);

    /**
     * @param AcceptHeader[] $acceptHeaders Sorted by quality
     * @param AcceptHeader[] $priorities    Configured priorities
     *
     * @return AcceptHeader[] Headers matched
     */
    abstract protected static function findMatches(array $acceptHeaders, array $priorities);

}
