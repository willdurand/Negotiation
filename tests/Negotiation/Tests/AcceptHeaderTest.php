<?php

namespace Negotiation\Tests;

use Negotiation\AcceptHeader;

class AcceptHeaderTest extends TestCase
{
    /**
     * @var AcceptHeader
     */
    private $acceptHeader;

    protected function call_private_method($class, $method, $object, $params) {
        $method = new \ReflectionMethod($class, $method);

        $method->setAccessible(TRUE);

        return $method->invokeArgs($object, $params);
    }

    public function testGetParameter()
    {
        $acceptHeader = new AcceptHeader('foo; q=1; 1.0 ; hello=world');

        $this->assertTrue($acceptHeader->hasParameter('hello'));
        $this->assertEquals('world', $acceptHeader->getParameter('hello'));

        $this->assertFalse($acceptHeader->hasParameter('unknown'));
        $this->assertNull($acceptHeader->getParameter('unknown'));
        $this->assertFalse($acceptHeader->getParameter('unknown', false));
    }

    /**
     * @dataProvider dataProviderForTestIsMediaRange
     */
    public function testIsMediaRange($value, $expected)
    {
        $header = new AcceptHeader($value);

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
     * @dataProvider dataProviderForParseParameters
     */
    public function testParseParameters($value, $expected)
    {
        $acceptHeader = new AcceptHeader($value);
        list($media_type, $parameters) = $this->call_private_method('\Negotiation\AcceptHeader', 'parseParameters', null, array($value));

        $this->assertCount(count($expected), $parameters);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $parameters);
            $this->assertEquals($value, $parameters[$key]);
        }
    }

    public static function dataProviderForParseParameters()
    {
        return array(
            array(
                'application/json ;q=1.0; level=2;foo= bar',
                array(
                    'q' => 1.0,
                    'level' => 2,
                    'foo'   => 'bar',
                ),
            ),
            array(
                'application/json ;q = 1.0; level = 2;     FOO  = bAr',
                array(
                    'q' => 1.0,
                    'level' => 2,
                    'foo'   => 'bAr',
                ),
            ),
            array(
                'application/json;q=1.0',
                array(
                    'q' => 1.0,
                ),
            ),
            array(
                'application/json;foo',
                array(),
            ),
        );
    }

    /**
     * @dataProvider dataProviderBuildParametersString
     */

    public function testBuildParametersString($value, $expected) {
        $string = $this->call_private_method('\Negotiation\AcceptHeader', 'buildParametersString', null, array($value));

        $this->assertEquals($string, $expected);
    }

    public static function dataProviderBuildParametersString()
    {
        return array(
            array(
                array(
                    'q' => '1.0',
                    'level' => '2',
                    'foo'   => 'bar',
                ),
                'q=1.0;level=2;foo=bar',
            ),
        );
    }

    /**
     * @dataProvider dataProviderForTestGetMediaType
     */
    public function testGetMediaType($acceptHeader, $expectedType) {

        $acceptHeader = new AcceptHeader($acceptHeader);
        $mt = $acceptHeader->getMediaType();

        $this->assertEquals($expectedType, $mt);

    }

    /**
     * @dataProvider dataProviderForTestGetMediaType
     */
    public function testGetMediaTypePassingParams($acceptHeader, $expectedType) {

        $parameters = $this->call_private_method('\Negotiation\AcceptHeader', 'parseParameters', null, array($acceptHeader)); 
        if (isset($parameters['q']))
            unset($parameters['q']);

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
