<?php

namespace Negotiation;

class AcceptLanguage extends BaseAccept
{
    private $basePart = null;
    private $subPart  = null;

    function __construct($value)
    {
        parent::__construct($value);

        $parts = explode('-', $this->type);

        if (count($parts) == 2) {
            $this->basePart   = $parts[0];
            $this->subPart    = $parts[1];
        } else if (count($parts) == 1) {
            $this->basePart   = $parts[0];
        } else {
            throw new \ParseTypeException('invalid language');
        }
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
