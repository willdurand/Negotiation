<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Header
{
    /**
     * @var float
     */
    private $quality = 1.0;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    protected $basePart = null;

    /**
     * @var string|null
     */
    protected $subPart = null;

    /**
     * @var string|null
     */
    protected $parameters = null;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        list($type, $parameters) = $this->parseParameters($value);

        $quality = 1.0;
        if (isset($parameters['q'])) {
            $quality = (float)$parameters['q'];
        }

        $this->value      = $type . ($parameters ? ";" . $this->buildParametersString($parameters, null, ';') : '');
        $this->type       = $type;
        $this->quality    = $quality;
        $this->parameters = $parameters;
    }

    /**
     * @param string $type
     *
     * @return array
     */

    protected static function parseParameters($acceptPart)
    {
        $parts = explode(';', preg_replace('/\s+/', '', $acceptPart));

        $type = array_shift($parts);

        $parameters = array();

        foreach ($parts as $part) {
            $part = explode('=', $part);

            if (2 !== count($part)) {
                continue;
            }

            $key = strtolower($part[0]);
            $parameters[$key] = $part[1];
        }

        return array($type, $parameters);
    }

    /**
     * @param string $parameters
     *
     * @return string
     */

    protected static function buildParametersString($params) {
        $parts = array();

        foreach ($params as $key => $val) {
            $parts[] = "$key=$val";
        }

        return implode(";", $parts);
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

}
