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
    public function getBest($acceptHeader, array $priorities = array())
    {
        $accepts = $this->parseAcceptHeader($acceptHeader);

        if (0 === count($accepts)) {
            return null;
        }

        if (0 !== count($priorities)) {
            $priorities = array_map('strtolower', $priorities);

            foreach ($accepts as $value => $quality) {
                if (0 !== $quality && in_array($value, $priorities)) {
                    return $value;
                }
            }
        }

        return key($accepts);
    }

    /**
     * @param string $acceptHeader
     *
     * @return array
     */
    public function parseAcceptHeader($acceptHeader)
    {
        $acceptHeader = preg_replace('/\s+/', '', $acceptHeader);
        $acceptParts  = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
            $acceptHeader, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $accepts = array();
        $index   = 0;
        foreach ($acceptParts as $accept) {
            $quality = 1;

            if (false !== strpos($accept, ';q=')) {
                list($accept, $quality) = explode(';q=', $accept);
            }

            $accepts[$accept] = array(
                'quality' => $quality,
                'index'   => $index
            );

            $index++;
        }

        uasort($accepts, function ($a, $b) {
            $qA = $a['quality'];
            $qB = $b['quality'];

            if ($qA === $qB) {
                return $a['index'] > $b['index'] ? 1 : -1;
            }

            return $qA > $qB ? -1 : 1;
        });

        return $accepts;
    }
}
