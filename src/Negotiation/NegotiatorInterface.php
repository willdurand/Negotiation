<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface NegotiatorInterface
{
    /**
     * @param string $header     A string containing an `Accept|Accept-*` header.
     * @param array  $priorities A set of priorities.
     *
     * @return AcceptHeader
     */
    public function getBest($header, array $priorities = array());
}
