<?php

namespace Negotiation;

class CharsetNegotiator extends AbstractNegotiator
{

    /**
     * @param strint $type
     *
     * @return AcceptCharsetHeader
     */
    protected function typeFactory($type)
    {
        return new AcceptCharsetHeader($type);
    }

}
