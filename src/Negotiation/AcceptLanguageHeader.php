<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptLanguageHeader extends Header
{
    /**
     * {@inheritdoc }
     */
    private static function setParts($value)
    {
        $parts = explode('-', $language);

        if (count($parts) == 2) {
            $this->basePart   = $parts[0];
            $this->subPart    = $parts[1];
        } if (count($parts) == 1) {
            $this->basePart   = $parts[0];
        } else {
            throw new Exception('invalid language type in header.');
        }
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->type;
    }
}
