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

    /**
     * {@inheritDoc}
     */
    public function getBest($header, array $priorities = array())
    {
        $acceptHeaders = $this->parseHeader($header);

        if (0 === count($acceptHeaders)) {
            return null;
        }

        if (0 !== count($priorities)) {
            $priorities = $this->sanitize($priorities);

            $wildcardAccept = null;
            foreach ($acceptHeaders as $accept) {
                if ($this->matchHeaderInPriorities($accept, $priorities)) {
                    return $accept;
                }

                if ('*' === $accept->getValue()) {
                    $wildcardAccept = $accept;
                }
            }

            if (null !== $wildcardAccept) {
                $value = reset($priorities);

                return new AcceptHeader($value, $wildcardAccept->getQuality(), $this->parseParameters($value));
            }
        }

        return reset($acceptHeaders);
    }

    /**
     * Checks whether an AcceptHeader is contained in priorities.  This implementation provide
     * matching between generic Accept-Language and localized priorities.
     * For instance, 'en' can be matched to 'en-US'.
     *
     * @param AcceptHeader $accept
     * @param array        $priorities
     *
     * @return boolean
     */
    protected function matchHeaderInPriorities($accept, $priorities)
    {
        $needle = strtolower($accept->getValue());

        $trimedPriorities = array_map(
            function($value){ return strtok($value, '-'); },
            $priorities
        );

        if (in_array($needle, $priorities) || in_array($needle, $trimedPriorities)) {
            return true;
        }

        return false;
    }
}
