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
    protected function match(AcceptCharsetHeader $charsetHeader, AcceptCharsetHeader $priority, $index) {
        $ac = $charsetHeader->getCharset();
        $pc = $priority->getCharset();

        if (!strcasecmp($ac, $pc) || $pc == '*') {
            return new Match($pc, $charsetHeader->getQuality(), 0, $index);
        }

        return null;
    }

}
