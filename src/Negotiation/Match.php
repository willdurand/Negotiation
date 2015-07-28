<?php

namespace Negotiation;

class Match
{
    public function __construct($quality, $score, $index)
    {
        $this->quality = $quality;
        $this->score   = $score;
        $this->index   = $index;
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

    /**
     * @param Match $a
     * @param Match $b
     *
     * @return int
     */
    public static function compare(Match $a, Match $b)
    {
        if ($a->quality != $b->quality) {
            return $a->quality > $b->quality ? -1 : 1;
        }

        if ($a->index != $b->index) {
            return $a->index > $b->index ? 1 : -1;
        }

        return 0;
    }
}
