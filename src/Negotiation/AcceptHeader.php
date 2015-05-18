<?php

namespace Negotiation;

require_once(__DIR__ . '/AbstractHeader.php');

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptHeader extends AbstractHeader
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param string $value
     * @param float  $quality       - only here for backwards compatibility
     * @param array  $parameters    - only here for backwards compatibility
     */
    public function __construct($value, $quality = null, array $parameters = array())
    {
        if ($quality !== null) {
            $this->value = $value;
            $this->quality = $quality;
            $this->parameters = $parameters;
        }

        $quality = 1.0;

        list($mediaType, $parameters) = $this->parseParameters($value);

        if (isset($parameters['q'])) {
            $quality = (float)$parameters['q'];
            unset($parameters['q']);
        } else {
            if ('*/*' === $mediaType) {
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
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * @return boolean
     */
    public function isMediaRange()
    {
        return false !== strpos($this->mediaType, '*');
    }
}
