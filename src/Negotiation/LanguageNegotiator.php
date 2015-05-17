<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class LanguageNegotiator extends Negotiator
{
    #/**
    # * {@inheritDoc}
    # */
    #protected function parseHeader($header)
    #{
    #    $acceptHeaders = array();

    #    $header      = preg_replace('/\s+/', '', $header);
    #    $acceptParts = explode(',', $header);

    #    $index    = 0;
    #    $catchAll = null;
    #    foreach ($acceptParts as $acceptPart) {
    #        if (!$acceptPart)
    #            continue;

    #        $acceptHeader = new AcceptHeader($acceptPart);

    #        if ('*' === $acceptHeader->getValue()) {
    #            $catchAll = $acceptHeader;
    #        } else {
    #            $acceptHeaders[] = array(
    #                'item'  => $acceptHeader,
    #                'index' => $index
    #            );
    #        }

    #        $index++;
    #    }

    #    return $this->sortAcceptHeaders($acceptHeaders, $catchAll);
    #}

    /**
     * {@inheritDoc}
     */
    protected function match(array $acceptHeaders, array $priorities = array())
    {
        $wildcardAccept  = null;

        $prioritiesSet   = array();
        $prioritiesSet[] = $priorities;
        $prioritiesSet[] = array_map(function ($priority) {
            return strtok($priority, '-');
        }, $priorities);

        foreach ($acceptHeaders as $accept) {
            foreach ($prioritiesSet as $availablePriorities) {
                $sanitizedPriorities = $this->sanitize($availablePriorities);

                if (false !== $found = array_search(strtolower($accept->getValue()), $sanitizedPriorities)) {
                    return $priorities[$found];
                }

                if ('*' === $accept->getValue()) {
                    $wildcardAccept = $accept;
                }
            }
        }

        if (null !== $wildcardAccept) {
            return reset($priorities);
        }
    }
}
