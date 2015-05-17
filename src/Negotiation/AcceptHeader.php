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
     * @var string
     */
    private $mediaType;

    /**
     * @var float
     */
    private $quality;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $baseType = null;

    /**
     * @var string|null
     */
    private $subType = null;

    const CATCH_ALL_VALUE = '*/*';

    /**
     * @param string $mediaType
     * @param float  $quality
     * @param array  $parameters
     */
    public function __construct($acceptPart)
    {
        $quality = 1.0;

        list($mediaType, $parameters) = $this->parseParameters($acceptPart);

        if (isset($parameters['q'])) {
            $quality = (float)$parameters['q'];
            unset($parameters['q']);
        } else {
            if (self::CATCH_ALL_VALUE === $mediaType) {
                $quality = 0.01;
            } elseif ('*' === substr($mediaType, -1)) {
                $quality = 0.02;
            }
        }

        $this->value      = $mediaType . ($parameters ? ";" . $this->buildParametersString($parameters, null, ';') : '');
        $this->mediaType  = $mediaType;
        $this->quality    = $quality;
        $this->parameters = $parameters;

        $parts = explode('/', $mediaType);

        if (count($parts) == 2) {
            $this->baseType   = $parts[0];
            $this->subType    = $parts[1];
        } if (count($parts) == 1) {
            $this->baseType   = $parts[0];
        } else {
            # TODO throw exception
        }
    }

    /**
     * @param string $parameters
     *
     * @return string
     */

    private static function buildParametersString($params) {
        $parts = array();

        foreach ($params as $key => $val) {
            $parts[] = "$key=$val";
        }

        return implode(";", $parts);
    }

    /**
     * @param string $mediaType
     *
     * @return array
     */

    private static function parseParameters($acceptPart)
    {
        $parts = explode(';', preg_replace('/\s+/', '', $acceptPart));

        $mediaType = array_shift($parts);

        $parameters = array();

        foreach ($parts as $part) {
            $part = explode('=', $part);

            if (2 !== count($part)) {
                continue;
            }

            $key = strtolower($part[0]);
            $parameters[$key] = $part[1];
        }

        return array($mediaType, $parameters);
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
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * @return string
     */
    public function getBaseType()
    {
        return $this->baseType;
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
        return false !== strpos($this->mediaType, '*');
    }
}
