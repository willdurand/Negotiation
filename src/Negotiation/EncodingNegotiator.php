<?php

namespace Negotiation;

class EncodingNegotiator extends AbstractNegotiator
{

    /**
     * @param strint $accept
     *
     * @return AcceptEncoding
     */
    protected function acceptFactory($accept)
    {
        return new AcceptEncoding($accept);
    }

}
