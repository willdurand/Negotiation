<?php

namespace Negotiation;

final class Matched
{
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

    public function __construct($quality, $score, $index)
    {
        $this->quality = $quality;
        $this->score   = $score;
        $this->index   = $index;
    }

    /**
     * @param Matched $a
     * @param Matched $b
     *
     * @return int
     */
    public static function compare(Matched $a, Matched $b)
    {
        if ($a->quality !== $b->quality) {
            return $a->quality > $b->quality ? -1 : 1;
        }

        if ($a->index !== $b->index) {
            return $a->index > $b->index ? 1 : -1;
        }

        return 0;
    }

    /**
     * @param array   $carry reduced array
     * @param Matched $match match to be reduced
     *
     * @return Matched[]
     */
    public static function reduce(array $carry, Matched $match)
    {
        if (!isset($carry[$match->index]) || $carry[$match->index]->score < $match->score) {
            $carry[$match->index] = $match;
        }

        return $carry;
    }
}
