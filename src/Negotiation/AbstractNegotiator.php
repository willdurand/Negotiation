<?php

namespace Negotiation;

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
            throw new \Exception('no priorities given');
        }

        if (!$header) {
            throw new \Exception('empty header given');
        }

        $headers = $this->parseHeader($header);

        $headers = $this->mapHeaders($headers);
        $priorities = $this->mapHeaders($priorities);

        $matches = $this->findMatches($headers, $priorities);

        # TODO what if only 1 or 2 items. will usort() work? read somewhere it won't...
        usort($matches, array($this, 'compare'));

        $match = array_shift($matches);
        if ($match === null) {
            return null;
        }

        return $priorities[$match->index];
    }

    /**
     * @param string $header A string that contains an `Accept*` header.
     *
     * @return Header[]
     */
    protected function parseHeader($header)
    {
        $res = preg_match_all('/(?:[^,"]*(?:"[^"]+")?)+[^,"]*/', $header, $matches);

        if (!$res) {
            throw new \Exception('failed to parse accept header');
        }

        return array_values(array_filter(array_map('trim', $matches[0])));
    }

    /**
     * @param array $priorities list of server priorities
     *
     * @return Header[]
     */
    private function mapHeaders($priorities)
    {
        return array_map(function($p) { return $this->typeFactory($p); }, $priorities);
    }

    /**
     * @param Header[]      $headers
     * @param Priority[]    $priorities    Configured priorities
     *
     * @return Match[] Headers matched
     */
    protected function findMatches(array $headerParts, array $priorities) {
        $matches = array();

        foreach ($priorities as $index => $p) {
            foreach ($headerParts as $h) {
                if ($match = $this->match($h, $p, $index))
                    $matches[] = $match;
            }
        }

        return $matches;
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

        if ($a->type == $b->type) {
            # priority goes to to more specific match
            if ($a->score < $b->score) {
                return 1;
            } else if ($a->score > $b->score) {
                return -1;
            }
        }

        if ($a->index < $b->index) {
            return -1;
        } else if ($a->index > $b->index) {
            return 1;
        }

        return 0;
    }

    /**
     * @param Header $header
     * @param Header $priority
     *
     * @return Match Headers matched
     */
    abstract protected function match(Header $header, Header $priority, $index);

    /**
     * TODO doc
     */
    abstract protected function typeFactory($header);

}
