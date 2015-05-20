<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptHeader extends Header
{

    /**
     * {@inheritdoc }
     */
    private static function setParts($value)
        $parts = explode('/', $value);

        if (count($parts) != 2) {
            throw new Exception('invalid media type in header.');
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
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isMediaRange()
    {
        return false !== strpos($this->mediaType, '*');
    }
}
