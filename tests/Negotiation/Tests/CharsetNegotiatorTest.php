<?php

namespace Negotiation\Tests;

use Negotiation\CharsetNegotiator;

class CharsetNegotiatorTest extends TestCase
{

    /**
     * @var CharsetNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new CharsetNegotiator();
    }


    public function testGetBestReturnsNullWithUnmatchedHeader()
    {
        $this->assertNull($this->negotiator->getBest('foo, bar, yo', array('baz')));
    }

    /**
     * 'bu' has the highest quality rating, but is non-existent,
     * so we expect the next highest rated 'fr' content to be returned.
     *
     * See: http://svn.apache.org/repos/asf/httpd/test/framework/trunk/t/modules/negotiation.t
     */
    public function testGetBestIgnoresNonExistentContent()
    {
        $acceptCharsetHeader = 'en; q=0.1, fr; q=0.4, bu; q=1.0';
        $acceptHeader         = $this->negotiator->getBest($acceptCharsetHeader, array('en', 'fr'));

        $this->assertInstanceOf('Negotiation\AcceptCharsetHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($acceptHeader, $priorities, $expected)
    {
        try {
            $acceptHeader = $this->negotiator->getBest($acceptHeader, $priorities);
            if (null === $acceptHeader) {
                $this->assertNull($expected);
            } else {
                $this->assertSame($expected, $acceptHeader->getValue());
            }
        } catch (Exception $e) {
            $this->assertSame($expected, $e->getMessage());
        }
    }

    public static function dataProviderForTestGetBest()
    {
        $pearCharsetHeader  = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7, *;q=0.5';
        $pearCharsetHeader2 = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7';

        return array(
            array($pearCharsetHeader, array( 'utf-8', 'big5', 'iso-8859-1', 'shift-jis',), 'iso-8859-1'),
            array($pearCharsetHeader, array( 'utf-8', 'big5', 'shift-jis',), 'utf-8'),
            array($pearCharsetHeader, array( 'Big5', 'shift-jis',), 'Big5'),
            array($pearCharsetHeader, array( 'shift-jis',), 'shift-jis'),
            array($pearCharsetHeader2, array( 'utf-8', 'big5', 'iso-8859-1', 'shift-jis',), 'iso-8859-1'),
            array($pearCharsetHeader2, array( 'utf-8', 'big5', 'shift-jis',), 'utf-8'),
            array($pearCharsetHeader2, array( 'Big5', 'shift-jis',), 'Big5'),
            array('utf-8;q=0.6,iso-8859-5;q=0.9', array( 'iso-8859-5', 'utf-8',), 'iso-8859-5'),
            array('', array( 'iso-8859-5', 'utf-8',), 'empty header given'),
            array('en, *;q=0.9', array('fr'), 'fr') 
        );
    }

    public function testGetBestRespectsPriorities()
    {
        $acceptHeader = $this->negotiator->getBest('foo, bar, yo', array('yo'));

        $this->assertInstanceOf('Negotiation\AcceptCharsetHeader', $acceptHeader);
        $this->assertEquals('yo', $acceptHeader->getValue());
    }

    public function testGetBestDoesNotMatchPriorities()
    {
        $acceptCharsetHeader = 'en, de';
        $priorities           = array('fr');

        $this->assertNull($this->negotiator->getBest($acceptCharsetHeader, $priorities));
    }

    /**
     * @dataProvider dataProviderForTestParseHeader
     */
    public function testParseHeader($header, $expected)
    {
        $accepts = $this->call_private_method('\Negotiation\CharsetNegotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertSame($expected, $accepts);
    }

    public static function dataProviderForTestParseHeader()
    {
        return array(
            array('*;q=0.3,ISO-8859-1,utf-8;q=0.7', array('*;q=0.3', 'ISO-8859-1', 'utf-8;q=0.7')),
            array('*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7', array('*;q=0.3', 'ISO-8859-1;q=0.7', 'utf-8;q=0.7')),
            array('*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7', array('*;q=0.3', 'utf-8;q=0.7', 'ISO-8859-1;q=0.7')),
            array('iso-8859-5, unicode-1-1;q=0.8', array('iso-8859-5', 'unicode-1-1;q=0.8')),
        );
    }

}
