<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptLanguageHeader extends Header
{
    private $basePart = null;
    private $subPart  = null;

    function __construct($value)
    {
        parent::__construct($value);

        $parts = explode('-', $value);

        if (count($parts) == 2) {
            $this->basePart   = $parts[0];
            $this->subPart    = $parts[1];
        } if (count($parts) == 1) {
            $this->basePart   = $parts[0];
        } else {
            throw new \Exception('invalid language type in header.');
        }
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->type;
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
