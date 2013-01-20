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

        $this->assertEquals('fu', $this->negotiator->getBest($acceptLanguageHeader));
    }

    /**
     * 'bu' has the highest quality rating, but is non-existant,
     * so we expect the next highest rated 'fr' content to be returned.
     *
     * See: http://svn.apache.org/repos/asf/httpd/test/framework/trunk/t/modules/negotiation.t
     */
    public function testGetBestIgnoresNonExistantContent()
    {
        $acceptLanguageHeader = 'en; q=0.1, fr; q=0.4, bu; q=1.0';

        $this->assertEquals('fr', $this->negotiator->getBest($acceptLanguageHeader, array('en', 'fr')));
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
        $this->assertEquals('yo', $this->negotiator->getBest('foo, bar, yo', array('yo')));
    }

    public function testGetBestInCaseInsensitive()
    {
        $this->assertEquals('yo', $this->negotiator->getBest('foo, bar, yo', array('YO')));
    }

    public function testGetBestWithQualities()
    {
        $this->assertEquals('bar', $this->negotiator->getBest('foo;q=0.1, bar, yo;q=0.9'));
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptHeader
     */
    public function testParseAcceptHeader($header, $expected)
    {
        $negotiator = new TestableNegotiator();

        $this->assertEquals($expected, array_map(function ($result) {
            return $result->getValue();
        }, $negotiator->parseAcceptHeader($header)));
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
