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

    /**
     * @var array
     */
    private $parameters;

    public function __construct($value, $quality, array $parameters = array())
    {
        $this->value      = $value;
        $this->quality    = $quality;
        $this->parameters = $parameters;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($key, $default = null)
    {
        return $this->hasParameter($key) ? $this->parameters[$key] : $default;
    }

    public function hasParameter($key)
    {
        return isset($this->parameters[$key]);
    }

    public function isMediaRange()
    {
        return false !== strpos($this->value, '*');
    }
}
