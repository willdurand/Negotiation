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

    /**
     * @param string $value
     * @param float  $quality
     * @param array  $parameters
     */
    public function __construct($value, $quality, array $parameters = array())
    {
        $this->value      = $value;
        $this->quality    = $quality;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        $parts     = explode(';', $this->value, 2);
        $mediaType = trim($parts[0], ' ');

        return $mediaType;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return float
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return string|null
     */
    public function getParameter($key, $default = null)
    {
        return $this->hasParameter($key) ? $this->parameters[$key] : $default;
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     * @return boolean
     */
    public function isMediaRange()
    {
        return false !== strpos($this->value, '*');
    }
}
