<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface FormatNegotiatorInterface extends NegotiatorInterface
{
    /**
     * Return the best format (as a string) based on a given `Accept` header,
     * and a set of priorities. Priorities are "formats" such as `json`, `xml`,
     * etc. or "mime types" such as `application/json`, `application/xml`, etc.
     *
     * @param string $acceptHeader A string containing an `Accept` header.
     * @param array  $priorities   A set of priorities.
     *
     * @return string|null
     */
    public function getBestFormat($acceptHeader, array $priorities = array());

    /**
     * Register a new format with its mime types.
     *
     * @param string  $format
     * @param array   $mimeTypes
     * @param boolean $override
     *
     * @return void
     */
    public function registerFormat($format, array $mimeTypes, $override = false);

    /**
     * Return the format for a given mime type, or null
     * if not found.
     *
     * @param string $mimeType
     *
     * @return string|null
     */
    public function getFormat($mimeType);

    /**
     * Ensure that any formats are converted to mime types.
     *
     * @param array $priorities
     *
     * @return array
     */
    public function normalizePriorities($priorities);
}
