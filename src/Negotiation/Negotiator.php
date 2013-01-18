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

            foreach ($accepts as $accept) {
                if (in_array(strtolower($accept->getValue()), $priorities)) {
                    return $accept->getValue();
                }
            }
        }

        return $accepts[0]->getValue();
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

        $index   = 0;
        $accepts = array();
        foreach ($acceptParts as $accept) {
            $quality = 1;

            if (false !== strpos($accept, ';q=')) {
                list($accept, $quality) = explode(';q=', $accept);
            }

            $accepts[] = array(
                'item'  => new AcceptHeader($accept, $quality),
                'index' => $index
            );

            $index++;
        }

        uasort($accepts, function ($a, $b) {
            $qA = $a['item']->getQuality();
            $qB = $b['item']->getQuality();

            if ($qA === $qB) {
                return $a['index'] > $b['index'] ? 1 : -1;
            }

            return $qA > $qB ? -1 : 1;
        });

        return array_map(function ($accept) {
            return $accept['item'];
        }, array_values($accepts));
    }
}
