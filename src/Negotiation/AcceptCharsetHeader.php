<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptCharsetHeader extends Header
{
    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->type;
    }
}
