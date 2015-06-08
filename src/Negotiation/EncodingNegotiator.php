<?php

namespace Negotiation;

class EncodingNegotiator extends AbstractNegotiator
{

    /**
     * @param strint $type
     *
     * @return AcceptEncodingHeader
     */
    protected function typeFactory($type)
    {
        return new AcceptEncodingHeader($type);
    }

}
