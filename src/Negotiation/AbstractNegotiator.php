<?php

namespace Negotiation;

use Negotiation\Exception\InvalidArgument;
use Negotiation\Exception\InvalidHeader;

abstract class AbstractNegotiator
{
    /**
     * @param string $header     A string containing an `Accept|Accept-*` header.
     * @param array  $priorities A set of server priorities.
     *
     * @return AcceptHeader|null best matching type
     */
    public function getBest($header, array $priorities, $strict = false)
    {
        if (empty($priorities)) {
            throw new InvalidArgument('A set of server priorities should be given.');
        }

        if (!$header) {
            throw new InvalidArgument('The header string should not be empty.');
        }

        // Once upon a time, two `array_map` calls were sitting there, but for
        // some reasons, they triggered `E_WARNING` time to time (because of
        // PHP bug [55416](https://bugs.php.net/bug.php?id=55416). Now, they
        // are gone.
        // See: https://github.com/willdurand/Negotiation/issues/81
        $acceptedHeaders = array();
        foreach ($this->parseHeader($header) as $h) {
            try {
                $acceptedHeaders[] = $this->acceptFactory($h);
            } catch (Exception\Exception $e) {
                if ($strict) {
                    throw $e;
                }
            }
        }
        $acceptedPriorities = array();
        foreach ($priorities as $p) {
            $acceptedPriorities[] = $this->acceptFactory($p);
        }
        $matches         = $this->findMatches($acceptedHeaders, $acceptedPriorities);
        $specificMatches = array_reduce($matches, 'Negotiation\AcceptMatch::reduce', []);

        usort($specificMatches, 'Negotiation\AcceptMatch::compare');

        $match = array_shift($specificMatches);

        return null === $match ? null : $acceptedPriorities[$match->index];
    }

    /**
     * @param string $header A string containing an `Accept|Accept-*` header.
     *
     * @return AcceptHeader[] An ordered list of accept header elements
     */
    public function getOrderedElements($header)
    {
        if (!$header) {
            throw new InvalidArgument('The header string should not be empty.');
        }

        $elements = array();
        $orderKeys = array();
        foreach ($this->parseHeader($header) as $key => $h) {
            try {
                $element = $this->acceptFactory($h);
                $elements[] = $element;
                $orderKeys[] = [$element->getQuality(), $key, $element->getValue()];
            } catch (Exception\Exception $e) {
                // silently skip in case of invalid headers coming in from a client
            }
        }

        // sort based on quality and then original order. This is necessary as
        // to ensure that the first in the list for two items with the same
        // quality stays in that order in both PHP5 and PHP7.
        uasort($orderKeys, function ($a, $b) {
            $qA = $a[0];
            $qB = $b[0];

            if ($qA == $qB) {
                return $a[1] <=> $b[1];
            }

            return ($qA > $qB) ? -1 : 1;
        });

        $orderedElements = [];
        foreach ($orderKeys as $key) {
            $orderedElements[] = $elements[$key[1]];
        }

        return $orderedElements;
    }

    /**
     * @param string $header accept header part or server priority
     *
     * @return AcceptHeader Parsed header object
     */
    abstract protected function acceptFactory($header);

    /**
     * @param AcceptHeader $header
     * @param AcceptHeader $priority
     * @param integer      $index
     *
     * @return AcceptMatch|null Headers matched
     */
    protected function match(AcceptHeader $header, AcceptHeader $priority, $index)
    {
        $ac = $header->getType();
        $pc = $priority->getType();

        $equal = !strcasecmp($ac, $pc);

        if ($equal || $ac === '*') {
            $score = 1 * $equal;

            return new AcceptMatch($header->getQuality() * $priority->getQuality(), $score, $index);
        }

        return null;
    }

    /**
     * @param string $header A string that contains an `Accept*` header.
     *
     * @return AcceptHeader[]
     */
    private function parseHeader($header)
    {
        $res = preg_match_all('/(?:[^,"]*+(?:"[^"]*+")?)+[^,"]*+/', $header, $matches);

        if (!$res) {
            throw new InvalidHeader(sprintf('Failed to parse accept header: "%s"', $header));
        }

        return array_values(array_filter(array_map('trim', $matches[0])));
    }

    /**
     * @param AcceptHeader[] $headerParts
     * @param Priority[]     $priorities  Configured priorities
     *
     * @return AcceptMatch[] Headers matched
     */
    private function findMatches(array $headerParts, array $priorities)
    {
        $matches = [];
        foreach ($priorities as $index => $p) {
            foreach ($headerParts as $h) {
                if (null !== $match = $this->match($h, $p, $index)) {
                    $matches[] = $match;
                }
            }
        }

        return $matches;
    }
}
