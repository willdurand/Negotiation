<?php

namespace Negotiation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface NegotiatorInterface
{
    /**
     * @param string $acceptHeader
     * @param array  $priorities
     *
     * @return AcceptHeader
     */
    public function getBest($acceptHeader, array $priorities = array());
}
