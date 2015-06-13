<?php

namespace Negotiation;

class BaseAccept
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
    protected $type;

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
            $this->quality = (float)$parameters['q'];
            unset($parameters['q']);
        }

        $this->value      = $value;
        $this->normalised = $type . ($parameters ? "; " . $this->buildParametersString($parameters) : '');
        $this->type       = $type;
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
                continue; # TODO throw exception here?
            }

            $key = strtolower(trim($part[0])); # TODO technically not allowed space around "=". throw exception?
            $parameters[$key] = trim($part[1], ' "');
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

        ksort($params);

        foreach ($params as $key => $val) {
            $parts[] = "$key=$val";
        }

        return implode("; ", $parts);
    }

    /**
     * @return string
     */
    public function getNormalisedValue()
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getQuality()
    {
        return $this->quality;
    }

}
