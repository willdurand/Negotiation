<?php

namespace Negotiation\Tests;

use Negotiation\Negotiator;
use Negotiation\AcceptHeader;
use Negotiation\Match;


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

#    public function testGetBestReturnsNullWithNullHeader()
#    {
#        $this->assertNull($this->negotiator->getBest(null));
#    }

    public function testGetBestThrowsExceptionInvalidMediaType()
    {
        try {
            $this->negotiator->getBest('asdf/qwer', array('f'));
            $this->assertTrue(false, 'exception not thrown');
        } catch (\Exception $e) {
            $this->assertSame('invalid media type.', $e->getMessage());
        }
    }

    public function testGetBestReturnsNullWithUnmatchedHeader()
    {
        $this->assertNull($this->negotiator->getBest('foo/aaa, bar/yyy, yo/sup', array('baz/asdf')));
    }

    public function testGetBestRespectsPriorities()
    {
        $acceptHeader = $this->negotiator->getBest('foo/aaa, bar/yyy, yo/sup', array('yo/sup'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('yo/sup', $acceptHeader->getValue());
    }

    public function testGetBestInCaseInsensitive()
    {
        $acceptHeader = $this->negotiator->getBest('foo/aaa, bar/yyy, yo/sup', array('YO/SuP'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('YO/SuP', $acceptHeader->getValue());
    }

    public function testGetBestWithQualities()
    {
        $acceptHeader = $this->negotiator->getBest('foo/aaa;q=0.1, bar/yyy, yo/sup;q=0.9', array('foo/aaa', 'bar/yyy', 'yo/sup'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('bar/yyy', $acceptHeader->getValue());
        $this->assertFalse($acceptHeader->hasParameter('q'));
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

    /**
     * @dataProvider dataProviderForTestFindMatches
     */
    public function testFindMatchesxxx($headerParts, $priorities, $expected)
    {
        $neg = new Negotiator();

        $matches = $this->call_private_method('\Negotiation\Negotiator', 'findMatches', $neg, array($headerParts, $priorities));

        $this->assertEquals($expected, $matches);
    }

    public static function dataProviderForTestFindMatches()
    {
        return array(
            array(
                array(new AcceptHeader('text/html; charset=UTF-8'), new AcceptHeader('image/png; foo=bar; q=0.7'), new AcceptHeader('*/*; foo=bar; q=0.4')),
                array(new AcceptHeader('text/html; charset=UTF-8'), new AcceptHeader('image/png; foo=bar'), new AcceptHeader('application/pdf')),
                array(
                    new Match('text/html', 1.0, 111, 0),
                    new Match('image/png', 0.7, 111, 1),
                    new Match('image/png', 0.4, 1,   1),
                )
            ),
            array(
                array(new AcceptHeader('text/html'), new AcceptHeader('image/*; q=0.7')),
                array(new AcceptHeader('text/html; asfd=qwer'), new AcceptHeader('image/png'), new AcceptHeader('application/pdf')),
                array(
                    new Match('text/html', 1.0, 110, 0),
                    new Match('image/png', 0.7, 100, 1),
                )
            ),
            array( # https://tools.ietf.org/html/rfc7231#section-5.3.2
                array(new AcceptHeader('text/*; q=0.3'), new AcceptHeader('text/html; q=0.7'), new AcceptHeader('text/html; level=1'), new AcceptHeader('text/html; level=2; q=0.4'), new AcceptHeader('*/*; q=0.5')),
                array(new AcceptHeader('text/html; level=1'), new AcceptHeader('text/html'), new AcceptHeader('text/plain'), new AcceptHeader('image/jpeg'), new AcceptHeader('text/html; level=2'), new AcceptHeader('text/html; level=3')),
                array(
                    new Match('text/html',      0.3,    100,    0),
                    new Match('text/html',      0.7,    110,    0),
                    new Match('text/html',      1.0,    111,    0),
                    new Match('text/html',      0.5,      0,    0),
                    new Match('text/html',      0.3,    100,    1),
                    new Match('text/html',      0.7,    110,    1),
                    new Match('text/html',      0.5,      0,    1),
                    new Match('text/plain',     0.3,    100,    2),
                    new Match('text/plain',     0.5,      0,    2),
                    new Match('image/jpeg',     0.5,      0,    3),
                    new Match('text/html',      0.3,    100,    4),
                    new Match('text/html',      0.7,    110,    4),
                    new Match('text/html',      0.4,    111,    4),
                    new Match('text/html',      0.5,      0,    4),
                    new Match('text/html',      0.3,    100,    5),
                    new Match('text/html',      0.7,    110,    5),
                    new Match('text/html',      0.5,      0,    5),
                )
            )
        );
    }

}
