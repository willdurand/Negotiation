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
        $acceptHeaders   = $this->parseAcceptHeader($acceptHeader);
        $priorities      = $this->sanitizePriorities($priorities);
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
                        return new AcceptHeader($priority, $accept->getQuality());
                    }
                }

                continue;
            }

            if (false === $catchAllEnabled &&
                self::CATCH_ALL_VALUE === $mimeType &&
                self::CATCH_ALL_VALUE !== $value = array_shift($priorities)
            ) {
                return new AcceptHeader($value, $accept->getQuality());
            }

            if (false === $pos = strpos($mimeType, ';')) {
                $pos = strpos($mimeType, '/');
            }

            $regex = '#^' . preg_quote(substr($mimeType, 0, $pos)) . '/#';

            foreach ($priorities as $priority) {
                if (self::CATCH_ALL_VALUE !== $priority && 1 === preg_match($regex, $priority)) {
                    return new AcceptHeader($priority, $accept->getQuality());
                }
            }
        }

        return array_shift($acceptHeaders) ?: null;
    }

    /**
     * Returns the best format (as astring) based on a given `Accept` header,
     * and a set of priorities.
     *
     * @param string $acceptHeader
     * @param array  $priorities
     *
     * @return string
     */
    public function getBestFormat($acceptHeader, array $priorities = array())
    {
        $priorities      = $this->sanitizePriorities($priorities);
        $catchAllEnabled = $this->isCatchAllEnabled($priorities);

        $mimeTypes = array();
        foreach ($priorities as $priority) {
            if (isset($this->formats[$priority])) {
                foreach ($this->formats[$priority] as $mimeType) {
                    $mimeTypes[] = $mimeType;
                }
            }
        }

        if ($catchAllEnabled) {
            $mimeTypes[] = self::CATCH_ALL_VALUE;
        }

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
     * @param array $priorities
     *
     * return boolean
     */
    private function isCatchAllEnabled(array $priorities)
    {
        return 0 === count($priorities) || in_array(self::CATCH_ALL_VALUE, $priorities);
    }
}
