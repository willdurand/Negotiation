<?php

namespace Negotiation;

class EncodingNegotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept): AcceptEncoding
    {
        return new AcceptEncoding($accept);
    }
}
