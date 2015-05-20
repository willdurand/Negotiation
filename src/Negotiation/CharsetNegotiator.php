<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class CharsetNegotiator extends AbstractNegotiator
{

    /**
     * @param string $header A string that contains an `Accept-Charset` header.
     *
     * @return AcceptCharsetHeader[]
     */
    protected function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (!$acceptParts) {
            throw new \Exception('failed to parse Accept-Languge header');
        }

        foreach ($acceptParts as $acceptPart) {
            $acceptHeaders[] = new AcceptCharsetHeader($acceptPart);
        }

        return $acceptHeaders;
    }

    /**
     * @param array $priorities list of server priorities
     *
     * @return AcceptCharsetHeader[]
     */
    protected function parsePriorities($priorities)
    {
        return array_map(function($p) { return new AcceptCharsetHeader($p); }, $priorities);
    }

    /**
     * @param AcceptCharsetHeader[] $languageHeaders Sorted by quality
     * @param Priority[] $priorities    Configured priorities
     *
     * @return Match[] Headers matched
     */
    protected function findMatches(array $languageHeaders, array $priorities) {
        $matches = array();
        $index = 0;

        foreach ($priorities as $p) {
            foreach ($languageHeaders as $a) {
                $at = $a->getCharset();
                $pt = $p->getCharset();

                $typeEqual = !strcasecmp($at, $pt);

                if ($typeEqual || $pt == '*') {
                    $matches[] = new Match($p->getCharset(), $a->getQuality(), 0, $index);
                }
            }

            $index++;
        }

        return $matches;
    }

}
