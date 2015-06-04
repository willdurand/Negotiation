<?php

namespace Negotiation\Tests;

use Negotiation\AcceptHeader;

class AcceptHeaderTest extends TestCase
{
    public function testGetParameter()
    {
        $acceptHeader = new AcceptHeader('foo/bar; q=1; hello=world');

        $this->assertTrue($acceptHeader->hasParameter('hello'));
        $this->assertEquals('world', $acceptHeader->getParameter('hello'));
        $this->assertFalse($acceptHeader->hasParameter('unknown'));
        $this->assertNull($acceptHeader->getParameter('unknown'));
        $this->assertFalse($acceptHeader->getParameter('unknown', false));
        $this->assertSame('world', $acceptHeader->getParameter('hello', 'goodbye'));
    }

    /**
     * @dataProvider dataProviderForTestGetNormalisedValue
     */
    public function testGetNormalisedValue($header, $expected)
    {
        $acceptHeader = new AcceptHeader($header);
        $actual = $acceptHeader->getNormalisedValue();
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
        $acceptHeader = new AcceptHeader($header);
        $actual = $acceptHeader->getType();
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
        $acceptHeader = new AcceptHeader($header);
        $actual = $acceptHeader->getValue();
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
