<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractNegotiator
{
    /**
     * @param string $header     A string containing an `Accept|Accept-*` header.
     * @param array  $priorities A set of server priorities.
     *
     * @return Header best matching type
     */
    public function getBest($header, array $priorities)
    {
        if (!$priorities) {
            throw new Exception('no priorities given');
        }

        if (!$header) {
            throw new Exception('empty header given');
        }

        $parts = $this->parseHeader($header);

        $priorities = $this->parsePriorities($priorities);

        $matches = $this->findMatches($parts, $priorities);

        usort($matches, array($this, 'compare'));

        $match = array_shift($matches);
        if ($match === null) {
            return null
        }

        return $priorities[$match->index];
    }

    /**
     * @param Match[] $a
     * @param Match[] $b
     *
     * @return int
     */
    private static function compare(array $a, array $b) {
        if ($a->quality > $b->quality) {
            return -1;
        } else if ($a->quality < $b->quality) {
            return 1;
        }

        # priority goes to to more specific match
        if ($a->type == $b->type) {
            if ($a->score < $b->score) {
                return 1;
            } else if ($a->score > $b->score) {
                return -1;
            }
        }

        if ($a->index < $b->index) {
            return 1;
        } else if ($a->index > $b->index) {
            return -1;
        }

        throw new Exception('failed to compare priorities.');
    }

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return Header[]
     */
    abstract protected static function parseHeader($header);

    /**
     * @param array $priorities list of server priorities
     *
     * @return Header[]
     */
    abstract protected static function parsePriorities($priorities);

    /**
     * @param Header[]
     * @param Header[]
     *
     * @return Match[] Headers matched
     */
    abstract protected static function findMatches(array $acceptHeaders, array $priorities);

}
