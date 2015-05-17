<?php

namespace Negotiation\Tests;

use Negotiation\Negotiator;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class NegotiatorTest extends TestCase
{

    protected function call_private_method($class, $method, $object, $params) {
        $method = new \ReflectionMethod($class, $method);

        $method->setAccessible(TRUE);

        return $method->invokeArgs($object, $params);
    }

    /**
     * @var Negotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new Negotiator();
    }

    public function testGetBestReturnsNullWithNullHeader()
    {
        $this->assertNull($this->negotiator->getBest(null));
    }

    public function testGetBestReturnsNullWithEmptyHeader()
    {
        $this->assertNull($this->negotiator->getBest(''));
    }

    public function testGetBestReturnsNullWithUnmatchedHeader()
    {
        $this->assertNull($this->negotiator->getBest('foo, bar, yo', array('baz')));
    }

    public function testGetBestRespectsPriorities()
    {
        $acceptHeader = $this->negotiator->getBest('foo, bar, yo', array('yo'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('yo', $acceptHeader->getValue());
    }

    public function testGetBestInCaseInsensitive()
    {
        $acceptHeader = $this->negotiator->getBest('foo, bar, yo', array('YO'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('YO', $acceptHeader->getValue());
    }

    public function testGetBestWithQualities()
    {
        $acceptHeader = $this->negotiator->getBest('foo;q=0.1, bar, yo;q=0.9');

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('bar', $acceptHeader->getValue());
        $this->assertFalse($acceptHeader->hasParameter('q'));
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($acceptHeader, $priorities, $expected, $parameters = array())
    {
        $acceptHeader = $this->negotiator->getBest($acceptHeader, $priorities);

        if (null === $expected) {
            $this->assertNull($acceptHeader);
        } else {
            $this->assertEquals($expected, $acceptHeader->getValue());

            foreach ($parameters as $k => $v) {
                $this->assertEquals($v, $acceptHeader->getParameter($k));
            }
        }
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeader
     */
    public function testParseAcceptHeader($header, $expected)
    {
        $accepts = $this->call_private_method('\Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertCount(count($expected), $accepts);
        $this->assertEquals($expected, array_map(function ($result) {
            return $result->getValue();
        }, $accepts));
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeaderWithQualities
     */
    public function testParseAcceptHeaderWithQualities($header, $expected)
    {
        $accepts = $this->call_private_method('\Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertEquals(count($expected), count($accepts));

        $i = 0;
        foreach ($expected as $value => $quality) {
            $this->assertEquals($value, $accepts[$i]->getValue());
            $this->assertEquals($quality, $accepts[$i]->getQuality());
            $i++;
        }
    }

    public static function dataProviderForTestParseAcceptHeader()
    {
        return array(
            array('gzip,deflate,sdch', array('gzip', 'deflate', 'sdch')),
            array("gzip, deflate\t,sdch", array('gzip', 'deflate', 'sdch')),
            array('"this;should,not=matter"', array('"this;should,not=matter"')),
            array('*;q=0.3,ISO-8859-1,utf-8;q=0.7', array('*', 'ISO-8859-1', 'utf-8')),
            array('*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7', array('*', 'ISO-8859-1', 'utf-8')),
            array('*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7', array('*', 'utf-8', 'ISO-8859-1')),
        );
    }

    public static function dataProviderForTestParseAcceptHeaderWithQualities()
    {
        return array(
            array('text/html;q=0.8', array('text/html' => 0.8)),
            array('text/html;foo=bar;q=0.8 ', array('text/html;foo=bar' => 0.8)),
            array('text/html;charset=utf-8; q=0.8', array('text/html;charset=utf-8' => 0.8)),
            array('text/html,application/xml;q=0.9,*/*;charset=utf-8; q=0.8', array('text/html' => 1.0, 'application/xml' => 0.9, '*/*;charset=utf-8' => 0.8)),
            array('text/html,application/xhtml+xml', array('text/html' => 1, 'application/xhtml+xml' => 1)),
            array('text/html, application/json;q=0.8, text/csv;q=0.7', array('text/html' => 1, 'application/json' => 0.8, 'text/csv' => 0.7)),
            array('iso-8859-5, unicode-1-1;q=0.8', array('iso-8859-5' => 1, 'unicode-1-1' => 0.8)),
            array('gzip;q=1.0, identity; q=0.5, *;q=0', array('gzip' => 1, 'identity' => 0.5, '*' => 0)),
        );
    }

    public static function dataProviderForTestGetBest()
    {
        $pearCharsetHeader  = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7, *;q=0.5';
        $pearCharsetHeader2 = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7';

        return array(
            array(
                $pearCharsetHeader,
                array(
                    'utf-8',
                    'big5',
                    'iso-8859-1',
                    'shift-jis',
                ),
                'iso-8859-1'
            ),
            array(
                $pearCharsetHeader,
                array(
                    'utf-8',
                    'big5',
                    'shift-jis',
                ),
                'utf-8'
            ),
            array(
                $pearCharsetHeader,
                array(
                    'Big5',
                    'shift-jis',
                ),
                'Big5'
            ),
            array(
                $pearCharsetHeader,
                array(
                    'shift-jis',
                ),
                'shift-jis'
            ),
            array(
                $pearCharsetHeader2,
                array(
                    'utf-8',
                    'big5',
                    'iso-8859-1',
                    'shift-jis',
                ),
                'iso-8859-1'
            ),
            array(
                $pearCharsetHeader2,
                array(
                    'utf-8',
                    'big5',
                    'shift-jis',
                ),
                'utf-8'
            ),
            array(
                $pearCharsetHeader2,
                array(
                    'Big5',
                    'shift-jis',
                ),
                'Big5'
            ),
            array(
                'utf-8;q=0.6,iso-8859-5;q=0.9',
                array(
                    'iso-8859-5',
                    'utf-8',
                ),
                'iso-8859-5'
            ),
            array(
                '',
                array(
                    'iso-8859-5',
                    'utf-8',
                ),
                null
            ),
# removed. no priorities makes no sense...
#            array(
#                'audio/*; q=0.2, audio/basic',
#                array(),
#                'audio/basic',
#            ),
        );
    }

    # https://tools.ietf.org/html/rfc7231#section-5.3.2
    public function testFindMatches() {
        $header = 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5';
        $acceptHeaders = $this->call_private_method('\Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $expectedMatches = array(
            #     value                     quality score
            array('text/html;level=1',      1.0,    111),   
            array('text/html',              0.7,    110),
            array('text/plain',             0.3,    100),
            array('image/jpeg',             0.5,    0),
            array('text/html;level=2',      0.4,    111),
            array('text/html;level=3',      0.7,    110),
        );

        $priorities = array_map(function($x) { return new \Negotiation\AcceptHeader($x[0]); }, $expectedMatches);

        $matches = $this->call_private_method('\Negotiation\Negotiator', 'findMatches', $this->negotiator, array($acceptHeaders, $priorities));

        $reducer = function($c, $new) {
            $value = $new[0]->getValue();

            if (!isset($c[$value])) {
                $c[$value] = $new;
            } else {
                $current = $c[$value];
                if (($current[2] < $new[2]) || ($current[2] == $new[2] && $current[1] < $new[1]))
                    $c[$value] = $new;
            }

            return $c;
        };

        # get best score for given value
        $matches = array_reduce($matches, $reducer, array());

        usort($expectedMatches, function($a, $b) { return strcmp($a[0], $b[0]); });
        usort($matches, function($a, $b) { return strcmp($a[0]->getValue(), $b[0]->getValue()); });

        $this->assertSame(count($matches), count($expectedMatches));

        for ($i = 0; $i < count($matches); $i++) {
            $this->assertSame($expectedMatches[$i][0], $matches[$i][0]->getValue());
            $this->assertSame($expectedMatches[$i][1], $matches[$i][1]);
            $this->assertSame($expectedMatches[$i][2], $matches[$i][2]);
        }
    }
}
