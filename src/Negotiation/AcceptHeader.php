<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptHeader
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var float
     */
    private $quality;

    public function __construct($value, $quality)
    {
        $this->value   = $value;
        $this->quality = $quality;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getQuality()
    {
        return $this->quality;
    }
}
