<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FormatNegotiator extends Negotiator
{
    // https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
    protected $formats = array(
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
        $acceptHeaders = $this->parseAcceptHeader($acceptHeader);
        $priorities    = array_map('strtolower', $priorities);

        foreach ($acceptHeaders as $accept) {
            $mimeType = $accept->getValue();

            if ('/*' !== substr($mimeType, -2)) {
                if (in_array($mimeType, $priorities)) {
                    return $accept;
                }

                continue;
            }

            if ('*/*' === $mimeType) {
                return new AcceptHeader(array_shift($priorities), 1);
            }

            $parts = explode('/', $mimeType);
            $regex = '#^' . preg_quote($parts[0]) . '/#';

            foreach ($priorities as $priority) {
                if (preg_match($regex, $priority)) {
                    return new AcceptHeader($priority, $accept->getQuality());
                }
            }
        }

        return reset($acceptHeaders) ?: null;
    }

    /**
     */
    public function getBestFormat($acceptHeader, array $priorities = array())
    {
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
