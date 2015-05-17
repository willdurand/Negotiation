<?php

namespace Negotiation\Tests;

use Negotiation\AcceptHeader;
use Negotiation\Negotiator;

class AcceptHeaderTest extends TestCase
{
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
}
