<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FormatNegotiator extends Negotiator implements FormatNegotiatorInterface
{
    // https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
    protected $formats = array(
        'html' => array('text/html', 'application/xhtml+xml'),
        'txt'  => array('text/plain'),
        'js'   => array('application/javascript', 'application/x-javascript', 'text/javascript'),
        'css'  => array('text/css'),
        'json' => array('application/json', 'application/x-json'),
        'jsonld' => array('application/ld+json'),
        'xml'  => array('text/xml', 'application/xml', 'application/x-xml'),
        'rdf'  => array('application/rdf+xml'),
        'atom' => array('application/atom+xml'),
        'rss'  => array('application/rss+xml'),
    );

    /**
     * {@inheritDoc}
     */
    public function getBestFormat($acceptHeader, array $priorities = array())
    {
        $mimeTypes = $this->normalizePriorities($priorities);

        if (null !== $accept = $this->getBest($acceptHeader, $mimeTypes)) {
            if (0.0 < $accept->getQuality() &&
                null !== $format = $this->getFormat($accept->getValue())
            ) {
                if (in_array($format, $priorities) || $this->isCatchAllEnabled($priorities)) {
                    return $format;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function registerFormat($format, array $mimeTypes, $override = false)
    {
        if (isset($this->formats[$format]) && false === $override) {
            throw new \InvalidArgumentException(sprintf(
                'Format "%s" already registered, and override was set to "false".',
                $format
            ));
        }

        $this->formats[$format] = $mimeTypes;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat($mimeType)
    {
        foreach ($this->formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }

        // strip parameters to, hopefully, find a matching format
        if (false !== $pos = strpos($mimeType, ';')) {
            $mimeType = substr($mimeType, 0, $pos);
        }

        foreach ($this->formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizePriorities($priorities)
    {
        $priorities = $this->sanitize($priorities);

        $mimeTypes = array();
        foreach ($priorities as $priority) {
            if (strpos($priority, '/')) {
                $mimeTypes[] = $priority;
                continue;
            }

            if (isset($this->formats[$priority])) {
                foreach ($this->formats[$priority] as $mimeType) {
                    $mimeTypes[] = $mimeType;
                }
            }
        }

        return $mimeTypes;
    }

    /**
     * @param array $priorities
     *
     * @return boolean
     */
    private function isCatchAllEnabled(array $priorities)
    {
        return 0 === count($priorities) || in_array(self::CATCH_ALL_VALUE, $priorities);
    }
}
