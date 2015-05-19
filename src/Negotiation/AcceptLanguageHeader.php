<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptLanguageHeader extends AbstractHeader
{
    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $quality = 1.0;

        list($language, $parameters) = $this->parseParameters($value);

        if (isset($parameters['q'])) {
            $quality = (float)$parameters['q'];
        }

        $this->value      = $language . (isset($parameters["q"]) ? ";q=" . $parameters["q"] : '');
        $this->language   = $language;
        $this->quality    = $quality;

        $parts = explode('-', $language);

        if (count($parts) == 2) {
            $this->baseType   = $parts[0];
            $this->subType    = $parts[1];
        } if (count($parts) == 1) {
            $this->baseType   = $parts[0];
        } else {
            throw new Exception('invalid media type in header.');
        }
    }

}
