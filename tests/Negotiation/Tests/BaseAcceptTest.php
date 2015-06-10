<?php

namespace Negotiation\Tests;

use Negotiation\BaseAccept;

class BaseAcceptTest extends TestCase
{
    /**
     * @dataProvider dataProviderForParseParameters
     */
    public function testParseParameters($value, $expected)
    {
        list($media_type, $parameters) = $this->call_private_method('\Negotiation\BaseAccept', 'parseParameters', null, array($value));

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
        $string = $this->call_private_method('\Negotiation\BaseAccept', 'buildParametersString', null, array($value));

        $this->assertEquals($string, $expected);
    }

    public static function dataProviderBuildParametersString()
    {
        return array(
            array(
                array(
                    'xxx' => '1.0',
                    'level' => '2',
                    'foo'   => 'bar',
                ),
                'foo=bar; level=2; xxx=1.0',
            ),
        );
    }

}
