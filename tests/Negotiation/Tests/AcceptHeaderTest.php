<?php

namespace Negotiation\Tests;

use Negotiation\AcceptHeader;
use Negotiation\Negotiator;

class AcceptHeaderTest extends TestCase
{

    protected function call_private_method($class, $method, $object, $params)
    {
        $method = new \ReflectionMethod($class, $method);

        $method->setAccessible(TRUE);

        return $method->invokeArgs($object, $params);
    }

    /**
     * @var AcceptHeader
     */
    private $acceptHeader;

    protected function setUp()
    {
        $this->acceptHeader = new AcceptHeader('foo', 1.0, array(
            'hello' => 'world',
        ));
    }

    public function testGetParameter()
    {
        $this->assertTrue($this->acceptHeader->hasParameter('hello'));
        $this->assertEquals('world', $this->acceptHeader->getParameter('hello'));

        $this->assertFalse($this->acceptHeader->hasParameter('unknown'));
        $this->assertNull($this->acceptHeader->getParameter('unknown'));
        $this->assertFalse($this->acceptHeader->getParameter('unknown', false));
    }

    /**
     * @dataProvider dataProviderForTestIsMediaRange
     */
    public function testIsMediaRange($value, $expected)
    {
        $header = new AcceptHeader($value, 1.0);

        $this->assertEquals($expected, $header->isMediaRange());
    }

    public static function dataProviderForTestIsMediaRange()
    {
        return array(
            array('text/*', true),
            array('*/*', true),
            array('application/json', false),
        );
    }

    /**
     * @dataProvider dataProviderForTestGetMediaType
     */
    public function testGetMediaType($acceptHeader, $expectedType)
    {
        $negotiator = new Negotiator();
        $parameters = $this->call_private_method('\Negotiation\Negotiator', 'parseParameters', $negotiator, array($acceptHeader));

        $acceptHeader = new AcceptHeader($acceptHeader, 1.0, $parameters);
        $mt = $acceptHeader->getMediaType();

        $this->assertEquals($expectedType, $mt);

    }

    public static function dataProviderForTestGetMediaType()
    {
        return array(
            array('text/html;hello=world', 'text/html'), # with param
            array('application/pdf', 'application/pdf'), # without param
            array('application/xhtml+xml;q=0.9', 'application/xhtml+xml'),
            array('text/plain; q=0.5', 'text/plain'),
            array('text/html;level=2;q=0.4', 'text/html'),
            array('text/html ; level = 2   ; q = 0.4', 'text/html'),
            array('text/*', 'text/*'),
            array('text/* ;q=1 ;level=2', 'text/*'),
            array('*/*', '*/*'),
            array('*/* ; param=555', '*/*'),
            array('TEXT/hTmL;leVel=2; Q=0.4', 'TEXT/hTmL'),

            # language
            array('da', 'da'),
            array('en-gb;q=0.8', 'en-gb'),
            array('en-GB;q=0.8', 'en-GB'),
            array('es;q=0.7', 'es'),
            array('fr ; q= 0.1', 'fr'),

            array('', null),
            array(null, null),
        );
    }
}
