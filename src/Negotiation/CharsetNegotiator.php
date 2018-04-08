<?php

namespace Negotiation;

class CharsetNegotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept): AcceptCharset
    {
        return new AcceptCharset($accept);
    }
}
