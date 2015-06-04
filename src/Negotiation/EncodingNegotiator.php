<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
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

    /**
     * {@inheritdoc}
     */
    protected function match(Header $charsetHeader, Header $priority, $index) {
    #TODO check this against rfc!!!
        $ac = $charsetHeader->getType();
        $pc = $priority->getType();

        $equal = !strcasecmp($ac, $pc);

        if ($equal || $ac == '*') {
            $score = 1 * $equal;
            return new Match($pc, $charsetHeader->getQuality(), $score, $index);
        }

        return null;
    }

}
