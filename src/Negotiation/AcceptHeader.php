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
    public function __construct($acceptPart, $quality = 1.0, array $parameters = array())
    {
        list($mediaType, $parsedParams) = $this->parseParameters($acceptPart);

        if (isset($parsedParams['q'])) {
            $quality = $parsedParams['q'];
            unset($parsedParams['q']);
        } else {
            if (self::CATCH_ALL_VALUE === $mediaType) {
                $quality = 0.01;
            } elseif ('*' === substr($mediaType, -1)) {
                $quality = 0.02;
            }
        }

        $this->value      = $mediaType . ($parsedParams ? ";" . $this->buildParametersString($parsedParams, null, ';') : '');
        $this->mediaType  = $mediaType;
        $this->quality    = $quality;
        $this->parameters = ($parameters ? $parameters : $parsedParams);
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        $parts = explode(';', $this->value, 2);
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
