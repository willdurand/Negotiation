<?php

namespace Negotiation;

class AcceptLanguage extends BaseAccept
{
    private $basePart;

    private $subPart;

    public function __construct($value)
    {
        parent::__construct($value);

        $parts = explode('-', $this->type);

        if (2 === count($parts)) {
            $this->basePart   = $parts[0];
            $this->subPart    = $parts[1];
        } elseif (1 === count($parts)) {
            $this->basePart   = $parts[0];
        } else {
            // TODO: this part is never reached...
            throw new InvalidLanguage();
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
