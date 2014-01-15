<?php

namespace Negotiation;

/**
 * @author Patrick van Kouteren <p.vankouteren@wedesignit.nl>
 */
class ApiFormatNegotiator extends FormatNegotiator
{

    /**
     * {@inheritDoc}
     */
    public function getBest($header, array $priorities = array())
    {
        $acceptHeaders = $this->parseHeader($header);

        if (0 === count($acceptHeaders)) {
            return null;
        }

        if (0 !== count($priorities)) {
            $priorities = $this->sanitize($priorities);

            $wildcardAccept = null;

            /**
             * @var AcceptHeader[] $acceptHeaders
             */
            foreach ($acceptHeaders as $accept) {
                foreach ($priorities as $pMimeType => $versions) {
                    if ($pMimeType == strtolower($accept->getValue())) {
                        if (in_array($accept->getVersion(), $versions)) {
                            return $accept;
                        }
                        // Nope, perhaps a wildcard?
                        if (in_array('*', $versions)) {
                            return $accept;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getBestFormat($acceptHeader, array $priorities = array())
    {
        $mimeTypes = $this->normalizePriorities($priorities);

        if (null !== $accept = $this->getBest($acceptHeader, $mimeTypes)) {
            if (null !== $format = $this->getFormat($accept->getValue())) {
                if (in_array($format, array_keys($priorities))) {
                    return $format;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function parseHeader($header)
    {
        $acceptHeaders = array();

        $header = preg_replace('/\s+/', '', $header);
        $acceptParts = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
            $header, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $index = 0;
        $catchAll = null;
        foreach ($acceptParts as $acceptPart) {
            $quality = 1.0;
            $parts = preg_split('/;\s*version=/i', $acceptPart, 0, PREG_SPLIT_NO_EMPTY);
            $parameters = $this->parseParameters($acceptPart);

            if (2 === count($parts)) {
                $value = $parts[0];
                $parameters['version'] = (float) $parts[1];
            } else {
                $value = $acceptPart;
            }

            if (self::CATCH_ALL_VALUE === $value) {
                $catchAll = new AcceptHeader($value, $quality, $parameters);
            } else {
                $acceptHeaders[] = array(
                    'item' => new AcceptHeader($value, $quality, $parameters),
                    'index' => $index
                );
            }

            $index++;
        }

        return $this->sortAcceptHeaders($acceptHeaders, $catchAll);
    }


    /**
     * @param array $values
     *
     * @return array Key: mime type, value: array of accepted versions
     */
    protected function sanitize(array $values)
    {
        $sane = array();
        foreach ($values as $value => $versions) {
            if (!is_array($versions)) {
                $value = $versions;
                $versions = array("*");
            }

            $sane[preg_replace('/\s+/', '', strtolower($value))] = array_map(function ($float) {
                return is_float($float) ? $float : ($float == '*' ? '*' : (float) $float);
            }, $versions);
        }

        return $sane;
    }

    /**
     * Ensure that any formats are converted to mime types.
     *
     * @param  array $priorities
     * @return array Key: mime type, value: array of accepted versions
     */
    public function normalizePriorities($priorities)
    {
        $priorities = $this->sanitize($priorities);

        $mimeTypes = array();
        foreach ($priorities as $priority => $versions) {
            if (strpos($priority, '/')) {
                $mimeTypes[$priority] = $versions;
                continue;
            }

            if (isset($this->formats[$priority])) {
                foreach ($this->formats[$priority] as $mimeType) {
                    $mimeTypes[$mimeType] = $versions;
                }
            }
        }

        return $mimeTypes;
    }


}
