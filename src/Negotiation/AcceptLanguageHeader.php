<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AcceptLanguageHeader extends AbstractHeader
{
    /**
     * @param string $value
     * @param float  $quality       - only here for backwards compatibility
     * @param array  $parameters    - only here for backwards compatibility
     */
    public function __construct($languageHeader)
    {
        $quality = 1.0;

        list($language, $parameters) = $this->parseParameters($languageHeader);

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
            # TODO throw exception
        }
    }

    /**                                                                                            
     * @return string                                                                              
     */                                                                                            
    public function getLanguage()                                                                 
    {                                                                                              
        return $this->language;                                                                   
    }

}
