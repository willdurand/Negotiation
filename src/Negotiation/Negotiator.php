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

        if (0 === count($acceptHeaders)) {
            return null;
        }

        if (0 === count($priorities)) {
            return reset($acceptHeaders);
        }

        $value = $this->match($acceptHeaders, $priorities);

        return empty($value) ? null : new AcceptHeader($value, 1.0, $this->parseParameters($value));
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
            $quality    = 1.0;
            $parts      = preg_split('/;\s*q=/i', $acceptPart, 0, PREG_SPLIT_NO_EMPTY);
            $parameters = $this->parseParameters($acceptPart);

            if (2 === count($parts)) {
                $value   = $parts[0];
                $quality = (float) $parts[1];
            } else {
                $value = $acceptPart;

                if (self::CATCH_ALL_VALUE === $value) {
                    $quality = 0.01;
                } elseif ('*' === substr($value, -1)) {
                    $quality = 0.02;
                }
            }

            if (self::CATCH_ALL_VALUE === $value) {
                $catchAll = new AcceptHeader($value, $quality, $parameters);
            } else {
                $acceptHeaders[] = array(
                    'item'  => new AcceptHeader($value, $quality, $parameters),
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
     * @param string $value
     *
     * @return array
     */
    protected function parseParameters($value)
    {
        $parts = explode(';', preg_replace('/\s+/', '', $value));
        array_shift($parts);

        $parameters = array();
        foreach ($parts as $part) {
            $part = explode('=', $part);

            if (2 !== count($part)) {
                continue;
            }

            if ('q' !== $key = strtolower($part[0])) {
                $parameters[$key] = $part[1];
            }
        }

        return $parameters;
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
            if (false !== $found = array_search(strtolower($accept->getValue()), $sanitizedPriorities)) {
                return $priorities[$found];
            }

            if ('*' === $accept->getValue()) {
                $wildcardAccept = $accept;
            }
        }

        if (null !== $wildcardAccept) {
            return reset($priorities);
        }

        return null;
    }
}
