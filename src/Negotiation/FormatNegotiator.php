<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FormatNegotiator extends Negotiator
{
    // https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
    private $formats = array(
        'html' => array('text/html', 'application/xhtml+xml'),
        'txt'  => array('text/plain'),
        'js'   => array('application/javascript', 'application/x-javascript', 'text/javascript'),
        'css'  => array('text/css'),
        'json' => array('application/json', 'application/x-json'),
        'xml'  => array('text/xml', 'application/xml', 'application/x-xml'),
        'rdf'  => array('application/rdf+xml'),
        'atom' => array('application/atom+xml'),
        'rss'  => array('application/rss+xml'),
    );

    /**
     * Register a new format with its mime types.
     *
     * @param string  $format
     * @param array   $mimeTypes
     * @param boolean $override
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
    public function getBest($acceptHeader, array $priorities = array())
    {
        $mimeTypes = array();
        foreach ($this->parseAcceptHeader($acceptHeader) as $accept) {
            $mimeTypes[$accept->getValue()] = $accept;
        }

        $catchAllEnabled = in_array('*/*', $priorities) || 0 === count($priorities);

        return $this->guessBestFormat($mimeTypes, $priorities, $catchAllEnabled);
    }

    /**
     * Guess the best format based on a set of mime types.
     *
     * @param array   $mimeTypes
     * @param array   $priorities
     * @param boolean $catchAllEnabled
     *
     * @return string|null
     */
    public function guessBestFormat(array $mimeTypes, array $priorities = array(), $catchAllEnabled = false)
    {
        $max  = reset($mimeTypes);
        $keys = array_keys($mimeTypes, $max);

        $formats = array();
        foreach ($keys as $mimeType) {
            unset($mimeTypes[$mimeType]);

            if ($mimeType === '*/*') {
                return reset($priorities);
            }

            if ($format = $this->getFormat($mimeType)) {
                if (false !== $priority = array_search($format, $priorities)) {
                    $formats[$format] = $priority;
                } elseif ($catchAllEnabled) {
                    $formats[$format] = count($priorities);
                }
            }
        }

        if (empty($formats) && !empty($mimeTypes)) {
            return $this->guessBestFormat($mimeTypes, $priorities, $catchAllEnabled);
        }

        asort($formats);

        return key($formats);
    }

    /**
     * Returns the format for a given mime type, or null
     * if not found.
     *
     * @param string $mimeType
     *
     * @return string|null
     */
    public function getFormat($mimeType)
    {
        foreach ($this->formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }
}
