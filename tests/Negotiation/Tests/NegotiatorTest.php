<?php

namespace Negotiation\Tests;

use Negotiation\Negotiator;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class NegotiatorTest extends TestCase
{
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
        $negotiator = new TestableNegotiator();
        $accepts    = $negotiator->parseHeader($header);

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
        $accepts    = $negotiator->parseHeader($header);

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
        $accepts    = $negotiator->parseHeader($header);

        $this->assertCount(count($expected), $accepts);

        $i = 0;
        foreach ($expected as $value => $quality) {
            $this->assertEquals($value,   $accepts[$i]->getValue());
            $this->assertEquals($quality, $accepts[$i]->getQuality());

            $i++;
        }
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeaderForBrowserBugs
     */
    public function testParseAcceptHeaderForBrowserBugs($header)
    {
        $negotiator = new TestableNegotiator();
        $accepts = $negotiator->parseHeader($header);

        $pass = FALSE;
        foreach ($accepts as $accept)
        {
            $value = $accept->getValue();
            $pass = $pass || in_array($value, array('text/html', 'application/xhtml+xml'));
        }
        $this->assertTrue($pass);
    }

    /**
     * @dataProvider dataProviderForParseParameters
     */
    public function testParseParameters($value, $expected)
    {
        $negotiator = new TestableNegotiator();
        $parameters = $negotiator->parseParameters($value);

        $this->assertCount(count($expected), $parameters);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $parameters);
            $this->assertEquals($value, $parameters[$key]);
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
            array(
                'audio/*; q=0.2, audio/basic',
                array(),
                'audio/basic',
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

    public static function dataProviderForTestParseAcceptHeaderForBrowserBugs()
    {
        return array(
            // @see https://bugs.webkit.org/show_bug.cgi?id=27267
            // Firefox 3.5 (2009)
          array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'),
            // IE8 (2009)
          array('image/gif, image/jpeg, image/pjpeg, image/pjpeg, application/x-shockwave-flash, application/xaml+xml, application/vnd.ms-xpsdocument, application/x-ms-xbap, application/x-ms-application, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, application/x-silverlight, */*'),
            // Opera (2009)
          array('text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1'),
            // Chrome (2009)
          array('application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'),
            // @see https://github.com/symfony/symfony/pull/564
            // Firefox 3.6 (2010)
          array('text/html,application/xhtml+xml,application/json,application/xml;q=0.9,*/*;q=0.8'),
            // Safari (2010)
          array('*/*'),
            // Opera (2010)
          array('image/jpeg,image/gif,image/x-xbitmap,text/html,image/webp,image/png,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.1'),
            // @see http://drupal.org/node/1716790
            // Safari (2010), iOS 4.2.1 (2012)
          array('application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'),
            // Android #1 (2012)
          array('application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'),
            // Android #2 (2012)
          array('text/xml,text/html,application/xhtml+xml,image/png,text/plain,*/*;q=0.8'),
        );
    }

    public static function dataProviderForParseParameters()
    {
        return array(
            array(
                'application/json ;q=1.0; level=2;foo= bar',
                array(
                    'level' => 2,
                    'foo'   => 'bar',
                ),
            ),
            array(
                'application/json ;q = 1.0; level = 2;     FOO  = bAr',
                array(
                    'level' => 2,
                    'foo'   => 'bAr',
                ),
            ),
            array(
                'application/json;q=1.0',
                array(),
            ),
            array(
                'application/json;foo',
                array(),
            ),
        );
    }
}

class TestableNegotiator extends Negotiator
{
    public function parseHeader($acceptHeader)
    {
        return parent::parseHeader($acceptHeader);
    }

    public function parseParameters($value)
    {
        return parent::parseParameters($value);
    }
}
