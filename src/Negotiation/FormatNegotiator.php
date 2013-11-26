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
     * {@inheritDoc}
     */
    public function getBest($header, array $priorities = array())
    {
        $acceptHeaders   = $this->parseHeader($header);
        $priorities      = $this->sanitize($priorities);
        $catchAllEnabled = $this->isCatchAllEnabled($priorities);

        foreach ($acceptHeaders as $accept) {
            $mimeType = $accept->getValue();

            if ('/*' !== substr($mimeType, -2)) {
                if (in_array($mimeType, $priorities)) {
                    return $accept;
                }

                $regex = '#^' . preg_quote($mimeType) . '#';

                foreach ($priorities as $priority) {
                    if (self::CATCH_ALL_VALUE !== $priority && 1 === preg_match($regex, $priority)) {
                        return new AcceptHeader($priority, $accept->getQuality(), $this->parseParameters($priority));
                    }
                }

                continue;
            }

            if (false === $catchAllEnabled &&
                self::CATCH_ALL_VALUE === $mimeType &&
                self::CATCH_ALL_VALUE !== $value = array_shift($priorities)
            ) {
                return new AcceptHeader($value, $accept->getQuality(), $this->parseParameters($value));
            }

            if (false === $pos = strpos($mimeType, ';')) {
                $pos = strpos($mimeType, '/');
            }

            $regex = '#^' . preg_quote(substr($mimeType, 0, $pos)) . '/#';

            foreach ($priorities as $priority) {
                if (self::CATCH_ALL_VALUE !== $priority && 1 === preg_match($regex, $priority)) {
                    return new AcceptHeader($priority, $accept->getQuality(), $this->parseParameters($priority));
                }
            }
        }

        return array_shift($acceptHeaders) ?: null;
    }

    /**
     * Returns the best format (as a string) based on a given `Accept` header,
     * and a set of priorities. Priorities are "formats" such as `json`, `xml`,
     * etc., not mime types.
     *
     * @param string $acceptHeader A string containing an `Accept` header.
     * @param array  $priorities   A set of priorities (formats).
     *
     * @return string
     */
    public function getBestFormat($acceptHeader, array $priorities = array())
    {
        $mimeTypes       = $this->getMimeTypes($priorities);
        $catchAllEnabled = $this->isCatchAllEnabled($priorities);

        if (null !== $accept = $this->getBest($acceptHeader, $mimeTypes)) {
            if (null !== $format = $this->getFormat($accept->getValue())) {
                if (in_array($format, $priorities) || $catchAllEnabled) {
                    return $format;
                }
            }
        }

        return null;
    }

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

    /**
     * Returns an array of mime types for the given set of formats.
     *
     * @param array $formats A set of formats.
     *
     * @return array
     */
    public function getMimeTypes(array $formats)
    {
        $formats         = $this->sanitize($formats);
        $catchAllEnabled = $this->isCatchAllEnabled($formats);

        $mimeTypes = array();
        foreach ($formats as $format) {
            if (isset($this->formats[$format])) {
                foreach ($this->formats[$format] as $mimeType) {
                    $mimeTypes[] = $mimeType;
                }
            }
        }

        if ($catchAllEnabled) {
            $mimeTypes[] = self::CATCH_ALL_VALUE;
        }

        return $mimeTypes;
    }

    /**
     * @param array $priorities
     *
     * return boolean
     */
    private function isCatchAllEnabled(array $priorities)
    {
        return 0 === count($priorities) || in_array(self::CATCH_ALL_VALUE, $priorities);
    }
}
