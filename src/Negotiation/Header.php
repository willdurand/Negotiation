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
    private $normalised;

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

        $type = trim(strtolower($type));

        if (isset($parameters['q'])) {
            $quality = (float)$parameters['q'];
        }

        $this->value      = $value;
        $this->normalised = $type . $this->buildParametersString($parameters);
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
        $parts = explode(';', $acceptPart);

        $type = array_shift($parts);

        $parameters = array();

        foreach ($parts as $part) {
            $part = explode('=', $part);

            if (2 !== count($part)) {
                continue;
            }

            $key = strtolower(trim($part[0])); # TODO technically not allowed space around "=". throw exception?
            $parameters[$key] = trim($part[1], ' "'); # param values can be quoted, too.
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
    public function getNormalised()
    {
        return $this->normalised;
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
