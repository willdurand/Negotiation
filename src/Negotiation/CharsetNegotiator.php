<?php

namespace Negotiation;

class CharsetNegotiator extends AbstractNegotiator
{

    /**
     * @param strint $accept
     *
     * @return AcceptCharset
     */
    protected function acceptFactory($accept)
    {
        return new AcceptCharset($accept);
    }

}
