<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class LanguageNegotiator extends Negotiator
{
    /**
     * {@inheritDoc}
     */
    protected function parseHeader($header)
    {
        $acceptHeaders = array();

        $header      = preg_replace('/\s+/', '', $header);
        $acceptParts = array();

        preg_match_all(
            '/(?<=[, ]|^)([a-zA-Z-]+|\*)(?:;q=([0-9.]+))?(?:$|\s*,\s*)/i',
            $header,
            $acceptParts,
            PREG_SET_ORDER
        );

        $index    = 0;
        $catchAll = null;
        foreach ($acceptParts as $acceptPart) {
            $value   = $acceptPart[1];
            $quality = isset($acceptPart[2]) ? (float) $acceptPart[2] : 1.0;

            if ('*' === $value) {
                $catchAll = new AcceptHeader($value, $quality);
            } else {
                $acceptHeaders[] = array(
                    'item'  => new AcceptHeader($value, $quality),
                    'index' => $index
                );
            }

            $index++;
        }

        return $this->sortAcceptHeaders($acceptHeaders, $catchAll);
    }
}
