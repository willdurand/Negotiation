<?php

namespace Negotiation;

class Match {
    function __construct($type, $quality, $score, $index) {
        $this->type = $type;
        $this->quality = $quality;
        $this->score = $score;
        $this->index = $index;
    }

    /**
     * @var string
     */
    public $type;

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
