<?php

namespace Negotiation;

class Match
{
    public function __construct($quality, $score, $index)
    {
        $this->quality = $quality;
        $this->score = $score;
        $this->index = $index;
    }

    /**
     * @var float
     */
    public $quality;

    /**
     * @var int
     */
    public $score;

    /**
     * @var int
     */
    public $index;
}
