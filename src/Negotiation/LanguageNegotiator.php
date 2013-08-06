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
            '/\s*([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0.[0-9]+))?,*\s*/i',
            $header,
            $acceptParts
        );

        if (!isset($acceptParts[1]) && !isset($acceptParts[4])) {
            return array();
        }

        $index    = 0;
        $catchAll = null;
        foreach (array_combine($acceptParts[1], $acceptParts[4]) as $value => $quality) {
            $quality = empty($quality) ? 1 : $quality;

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
