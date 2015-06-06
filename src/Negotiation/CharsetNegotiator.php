<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
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

    /**
     * {@inheritdoc}
     */
    protected function match(Header $charsetHeader, Header $priority, $index) {
        $ac = $charsetHeader->getType();
        $pc = $priority->getType();

        if (!strcasecmp($ac, $pc) || $ac == '*') {
            return new Match($charsetHeader->getQuality(), 0, $index);
        }

        return null;
    }

}
