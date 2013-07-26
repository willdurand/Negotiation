<?php

namespace Negotiation\Tests;

use Negotiation\Negotiator;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class NegotiatorTest extends TestCase
{
    protected function setUp()
    {
        $this->negotiator = new Negotiator();
    }

    /**
     * 'fu' has a quality rating of 0.9 which is higher than the rest
     * we expect Negotiator to return the 'fu' content.
     *
     * See: http://svn.apache.org/repos/asf/httpd/test/framework/trunk/t/modules/negotiation.t
     */
    public function testGetBestUsesQuality()
    {
        $acceptLanguageHeader = 'en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2';
        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader);

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('fu', $acceptHeader->getValue());
    }

    /**
     * 'bu' has the highest quality rating, but is non-existent,
     * so we expect the next highest rated 'fr' content to be returned.
     *
     * See: http://svn.apache.org/repos/asf/httpd/test/framework/trunk/t/modules/negotiation.t
     */
    public function testGetBestIgnoresNonExistentContent()
    {
        $acceptLanguageHeader = 'en; q=0.1, fr; q=0.4, bu; q=1.0';
        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader, array('en', 'fr'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    public function testGetBestReturnsNullWithNullHeader()
    {
        $this->assertNull($this->negotiator->getBest(null));
    }

    public function testGetBestReturnsNullWithEmptyHeader()
    {
        $this->assertNull($this->negotiator->getBest(''));
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
        $this->assertEquals('yo', $acceptHeader->getValue());
    }

    public function testGetBestWithQualities()
    {
        $acceptHeader = $this->negotiator->getBest('foo;q=0.1, bar, yo;q=0.9');

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('bar', $acceptHeader->getValue());
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($acceptHeader, $priorities, $expected)
    {
        $acceptHeader = $this->negotiator->getBest($acceptHeader, $priorities);

        $this->assertEquals($expected, $acceptHeader->getValue());
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeader
     */
    public function testParseAcceptHeader($header, $expected)
    {
        $negotiator = new TestableNegotiator();
        $accepts    = $negotiator->parseAcceptHeader($header);

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
        $negotiator = new TestableNegotiator();
        $accepts    = $negotiator->parseAcceptHeader($header);

        $this->assertEquals(count($expected), count($accepts));

        $i = 0;
        foreach ($expected as $value => $quality) {
            $this->assertEquals($value, $accepts[$i]->getValue());
            $this->assertEquals($quality, $accepts[$i]->getQuality());
            $i++;
        }
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeaderEnsuresPrecedence
     */
    public function testParseAcceptHeaderEnsuresPrecedence($header, $expected)
    {
        $negotiator = new TestableNegotiator();
        $accepts    = $negotiator->parseAcceptHeader($header);

        $this->assertCount(count($expected), $accepts);

        $i = 0;
        foreach ($expected as $value => $quality) {
            $this->assertEquals($value,   $accepts[$i]->getValue());
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
            array('*;q=0.3,ISO-8859-1,utf-8;q=0.7', array('ISO-8859-1', 'utf-8', '*')),
            array('*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7',  array('ISO-8859-1', 'utf-8', '*')),
            array('*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7',  array('utf-8', 'ISO-8859-1', '*')),
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
                'ISO-8859-1'
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
                'ISO-8859-1'
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
        );
    }

    public static function dataProviderForTestParseAcceptHeaderEnsuresPrecedence()
    {
        return array(
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(
                    'text/html;level=1' => 1,
                    'text/html;level=2' => 0.4,
                    'text/html'         => 0.7,
                    'text/*'            => 0.3,
                    '*/*'               => 0.5,
                )
            ),
            array(
                'text/html,application/xhtml+xml,application/xml;q=0.9,text/*;q=0.7,*/*,image/gif; q=0.8, image/jpeg; q=0.6, image/*',
                array(
                    'text/html'             => 1,
                    'application/xhtml+xml' => 1,
                    'application/xml'       => 0.9,
                    'image/gif'             => 0.8,
                    'text/*'                => 0.7,
                    'image/jpeg'            => 0.6,
                    'image/*'               => 0.02,
                    '*/*'                   => 0.01,
                )
            ),
        );
    }
}

class TestableNegotiator extends Negotiator
{
    public function parseAcceptHeader($acceptHeader)
    {
        return parent::parseAcceptHeader($acceptHeader);
    }
}
