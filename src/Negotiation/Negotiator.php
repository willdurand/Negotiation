<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Negotiator implements NegotiatorInterface
{
    const CATCH_ALL_VALUE = '*/*';

    /**
     * {@inheritDoc}
     */
    public function getBest($header, array $priorities = array())
    {
        $acceptHeaders = $this->parseHeader($header);

        if (empty($acceptHeaders)) {
            return null;
        } elseif (empty($priorities)) {
            return reset($acceptHeaders);
        }

        $value = $this->match($acceptHeaders, $priorities);

        return empty($value) ? null : new AcceptHeader($value);
    }

    /**
     * @param string $header A string that contains an `Accept|Accept-*` header.
     *
     * @return AcceptHeader[]
     */
    protected function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
            $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $index    = 0;
        $catchAll = null;
        foreach ($acceptParts as $acceptPart) {
            $acceptHeader = new AcceptHeader($acceptPart);

            if (self::CATCH_ALL_VALUE === $acceptHeader->getValue()) {
                $catchAll = $acceptHeader;
            } else {
                $acceptHeaders[] = array(
                    'item'  => $acceptHeader,
                    'index' => $index
                );
            }

            $index++;
        }

        return $this->sortAcceptHeaders($acceptHeaders, $catchAll);
    }

    /**
     * @param array        $acceptHeaders A set of AcceptHeader objects to sort.
     * @param AcceptHeader $catchAll      A special AcceptHeader that represents the "catch all".
     *
     * @return AcceptHeader[]
     */
    protected function sortAcceptHeaders(array $acceptHeaders, AcceptHeader $catchAll = null)
    {
        uasort($acceptHeaders, function ($a, $b) {
            $qA = $a['item']->getQuality();
            $qB = $b['item']->getQuality();

            $vA = $a['item']->getValue();
            $vB = $b['item']->getValue();

            // put specific media type before the classic one
            // e.g. `text/html;level=1` first, then `text/html`
            if (strstr($vA, $vB)) {
                return -1;
            }

            if ($qA === $qB) {
                return $a['index'] > $b['index'] ? 1 : -1;
            }

            return $qA > $qB ? -1 : 1;
        });

        // put the catch all header at the end if available
        if (null !== $catchAll) {
            array_push($acceptHeaders, array('item' => $catchAll));
        }

        return array_map(function ($accept) {
            return $accept['item'];
        }, array_values($acceptHeaders));
    }

    /**
     * @param array $values
     *
     * @return array
     */
    protected function sanitize(array $values)
    {
        return array_map(function ($value) {
            return preg_replace('/\s+/', '', strtolower($value));
        }, $values);
    }

    /**
     * @param AcceptHeader[] $acceptHeaders Sorted by quality
     * @param array          $priorities    Configured priorities
     *
     * @return string|null Header string matched
     */
    protected function match(array $acceptHeaders, array $priorities = array())
    {
        $wildcardAccept      = null;
        $sanitizedPriorities = $this->sanitize($priorities);

        foreach ($acceptHeaders as $accept) {
            if (false !== $found = array_search($value = strtolower($accept->getValue()), $sanitizedPriorities)) {
                return $priorities[$found];
            } elseif ('*' === $value) {
                $wildcardAccept = $accept;
            }
        }

        if (null !== $wildcardAccept) {
            return reset($priorities);
        }

        return null;
    }

}
