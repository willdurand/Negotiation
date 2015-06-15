<?php

namespace Negotiation;

class Accept extends BaseAccept
{
    private $basePart = null;
    private $subPart  = null;

    function __construct($value)
    {
        parent::__construct($value);

        $parts = explode('/', $this->type);

        if (count($parts) != 2 || !$parts[0] || !$parts[1]) {
            throw new ParseTypeException('invalid media type.');
        }

        $this->basePart = $parts[0];
        $this->subPart  = $parts[1];
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
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
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
    public function getSubPart()
    {
        return $this->subPart;
    }

    /**
     * @return string
     */
    public function getBasePart()
    {
        return $this->basePart;
    }

}
