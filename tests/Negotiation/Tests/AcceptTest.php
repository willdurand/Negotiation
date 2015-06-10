<?php

namespace Negotiation\Tests;

use Negotiation\Accept;

class AcceptTest extends TestCase
{
    public function testGetParameter()
    {
        $accept = new Accept('foo/bar; q=1; hello=world');

        $this->assertTrue($accept->hasParameter('hello'));
        $this->assertEquals('world', $accept->getParameter('hello'));
        $this->assertFalse($accept->hasParameter('unknown'));
        $this->assertNull($accept->getParameter('unknown'));
        $this->assertFalse($accept->getParameter('unknown', false));
        $this->assertSame('world', $accept->getParameter('hello', 'goodbye'));
    }

    /**
     * @dataProvider dataProviderForTestGetNormalisedValue
     */
    public function testGetNormalisedValue($header, $expected)
    {
        $accept = new Accept($header);
        $actual = $accept->getNormalisedValue();
        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderForTestGetNormalisedValue()
    {
        return array(
            array('text/html; z=y; a=b; c=d', 'text/html; a=b; c=d; z=y'),
            array('application/pdf; q=1; param=p',  'application/pdf; param=p')
        );
    }

    /**
     * @dataProvider dataProviderForGetType
     */
    public function testGetType($header, $expected)
    {
        $accept = new Accept($header);
        $actual = $accept->getType();
        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderForGetType()
    {
        return array(
           array('text/html;hello=world', 'text/html'),
           array('application/pdf', 'application/pdf'),
        );
    }

    /**
     * @dataProvider dataProviderForGetValue
     */
    public function testGetValue($header, $expected)
    {
        $accept = new Accept($header);
        $actual = $accept->getValue();
        $this->assertEquals($expected, $actual);

    }

    public static function dataProviderForGetValue()
    {
        return array(
            array('text/html;hello=world  ;q=0.5', 'text/html;hello=world  ;q=0.5'),
            array('application/pdf', 'application/pdf'),
        );
    }
}
