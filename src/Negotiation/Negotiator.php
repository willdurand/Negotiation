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
    public function getBest($acceptHeader, array $priorities = array())
    {
        $accepts = $this->parseAcceptHeader($acceptHeader);

        if (0 === count($accepts)) {
            return null;
        }

        if (0 !== count($priorities)) {
            $priorities = array_map('strtolower', $priorities);

            $wildcardAccept = null;
            foreach ($accepts as $accept) {
                if (in_array(strtolower($accept->getValue()), $priorities)) {
                    return $accept;
                }

                if ('*' === $accept->getValue()) {
                    $wildcardAccept = $accept;
                }
            }

            if (null !== $wildcardAccept) {
                return new AcceptHeader(reset($priorities), $wildcardAccept->getQuality());
            }
        }

        return reset($accepts);
    }

    /**
     * @param string $acceptHeader
     *
     * @return array[AcceptHeader]
     */
    protected function parseAcceptHeader($acceptHeader)
    {
        $acceptHeader = preg_replace('/\s+/', '', $acceptHeader);
        $acceptParts  = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
            $acceptHeader, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $index    = 0;
        $accepts  = array();
        $catchAll = null;
        foreach ($acceptParts as $accept) {
            $quality = 1;

            if (false !== strpos($accept, ';q=')) {
                list($accept, $quality) = explode(';q=', $accept);
            } else {
                if (self::CATCH_ALL_VALUE === $accept) {
                    $quality = 0.01;
                } elseif ('*' === substr($accept, -1)) {
                    $quality = 0.02;
                }
            }

            if (self::CATCH_ALL_VALUE === $accept) {
                $catchAll = new AcceptHeader($accept, $quality);
            } else {
                $accepts[] = array(
                    'item'  => new AcceptHeader($accept, $quality),
                    'index' => $index
                );
            }

            $index++;
        }

        uasort($accepts, function ($a, $b) {
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
            array_push($accepts, array('item' => $catchAll));
        }

        return array_map(function ($accept) {
            return $accept['item'];
        }, array_values($accepts));
    }
}
