<?php

namespace Negotiation;

abstract class AbstractNegotiator
{
    /**
     * @param string $header     A string containing an `Accept|Accept-*` header.
     * @param array  $priorities A set of server priorities.
     *
     * @return BaseAccept best matching type
     */
    public function getBest($header, array $priorities)
    {
        if (!$priorities) {
            throw new \Exception('no priorities given'); 
        }

        if (!$header) {
            throw new \Exception('empty header given'); 
        }

        $headers = self::parseHeader($header);

        $headers = array_map(array($this, 'acceptFactory'), $headers);
        $priorities  = array_map(array($this, 'acceptFactory'), $priorities);

        $matches = self::findMatches($headers, $priorities);

        # find most specific match for each priority
        $specific_matches = array_reduce($matches, array($this, 'reduce'), array());

        usort($specific_matches, array($this, 'compare'));

        $match = array_shift($specific_matches);

        if ($match === null) {
            return null;
        }

        return $priorities[$match->index];
    }

    /**
     * @param string $header A string that contains an `Accept*` header.
     *
     * @return BaseAccept[]
     */
    private static function parseHeader($header)
    {
        $res = preg_match_all('/(?:[^,"]*(?:"[^"]+")?)+[^,"]*/', $header, $matches);

        if (!$res) {
            throw new \Exception('failed to parse accept header');
        }

        return array_values(array_filter(array_map('trim', $matches[0])));
    }

    /**
     * @param BaseAccept[]      $headers
     * @param Priority[]    $priorities    Configured priorities
     *
     * @return Match[] Headers matched
     */
    private static function findMatches(array $headerParts, array $priorities) {
        $matches = array();

        foreach ($priorities as $index => $p) {
            foreach ($headerParts as $h) {
                if ($match = static::match($h, $p, $index))
                    $matches[] = $match;
            }
        }

        return $matches;
    }

    /**
     * @param array $carry reduced array
     * @param Match $match match to be reduced
     *
     * @return Match[]
     */
    private static function reduce(array $carry, Match $match) {
        if (!isset($carry[$match->index]) || $carry[$match->index]->score < $match->score) {
            $carry[$match->index] = $match;
        }

        return $carry;
    }

    /**
     * @param Match[] $a
     * @param Match[] $b
     *
     * @return int
     */
    private static function compare(Match $a, Match $b) {
        if ($a->quality > $b->quality) {
            return -1;
        } else if ($a->quality < $b->quality) {
            return 1;
        }

        if ($a->index < $b->index) {
            return -1;
        } else if ($a->index > $b->index) {
            return 1;
        }

        return 0;
    }

    /**
     * @param BaseAccept $header
     * @param BaseAccept $priority
     *
     * @return Match Headers matched
     */
    protected static function match(BaseAccept $header, BaseAccept $priority, $index) {
        $ac = $header->getType();
        $pc = $priority->getType();

        $equal = !strcasecmp($ac, $pc);

        if ($equal || $ac == '*') {
            $score = 1 * $equal;

            return new Match($header->getQuality(), $score, $index);
        }

        return null;
    }

    /**
     * @param string $header type string
     *
     * @return BaseAccept[] parsed header objects
     */
    abstract protected function acceptFactory($header);

}
