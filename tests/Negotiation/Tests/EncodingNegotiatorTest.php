<?php

namespace Negotiation\Tests;

use Negotiation\EncodingNegotiator;

class EncodingNegotiatorTest extends TestCase
{

    /**
     * @var EncodingNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new EncodingNegotiator();
    }


    public function testGetBestReturnsNullWithUnmatchedHeader()
    {
        $this->assertNull($this->negotiator->getBest('foo, bar, yo', array('baz')));
    }

    /**
     * 'fu' has a quality rating of 0.9 which is higher than the rest
     * we expect Negotiator to return the 'fu' content.
     *
     * See: http://svn.apache.org/repos/asf/httpd/test/framework/trunk/t/modules/negotiation.t
     */
    public function testGetBestUsesQuality()
    {
        $acceptEncodingHeader = 'en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2';
        $acceptHeader         = $this->negotiator->getBest($acceptEncodingHeader);

        $this->assertInstanceOf('Negotiation\AcceptEncodingHeader', $acceptHeader);
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
        $acceptEncodingHeader = 'en; q=0.1, fr; q=0.4, bu; q=1.0';
        $acceptHeader         = $this->negotiator->getBest($acceptEncodingHeader, array('en', 'fr'));

        $this->assertInstanceOf('Negotiation\AcceptEncodingHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($acceptHeader, $priorities, $expected)
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
     * Given a accept header containing a generic language (here 'en')
     *  And priorities containing a localized version of that language
     * Then the best language is mapped to 'en'
     */
    public function testGenericEncodingAreMappedToSpecific()
    {
        $acceptEncodingHeader = 'fr-FR, en;q=0.8';
        $priorities           = array('en-US', 'de-DE');

        $acceptHeader = $this->negotiator->getBest($acceptEncodingHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptEncodingHeader', $acceptHeader);
        $this->assertEquals('en-US', $acceptHeader->getValue());
    }

    public function testGetBestWithWildcard()
    {
        $acceptEncodingHeader = 'en, *;q=0.9';
        $priorities           = array('fr');

        $acceptHeader = $this->negotiator->getBest($acceptEncodingHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptEncodingHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    public function testGetBestRespectsPriorities()
    {
        $acceptHeader = $this->negotiator->getBest('foo, bar, yo', array('yo'));

        $this->assertInstanceOf('Negotiation\AcceptEncodingHeader', $acceptHeader);
        $this->assertEquals('yo', $acceptHeader->getValue());
    }

    public function testGetBestDoesNotMatchPriorities()
    {
        $acceptEncodingHeader = 'en, de';
        $priorities           = array('fr');

        $this->assertNull($this->negotiator->getBest($acceptEncodingHeader, $priorities));
    }

    public static function dataProviderForTestGetBest()
    {
        return array();

# TODO tests something like this...
#        $pearCharsetHeader  = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7, *;q=0.5';
#        $pearCharsetHeader2 = 'ISO-8859-1, Big5;q=0.6,utf-8;q=0.7';
#
#        return array(
#            array(
#                $pearCharsetHeader,
#                array(
#                    'utf-8',
#                    'big5',
#                    'iso-8859-1',
#                    'shift-jis',
#                ),
#                'iso-8859-1'
#            ),
#        );
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptEncodingHeader
     */
    public function testParseAcceptEncodingHeader($header, $expected)
    {
        $accepts = $this->call_private_method('\Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertCount(count($expected), $accepts);
        $this->assertEquals($expected, array_map(function ($result) {
            return $result->getValue();
        }, $accepts));
    }

    public static function dataProviderForTestParseAcceptEncodingHeader()
    {
        return array(
            array('gzip,deflate,sdch', array('gzip', 'deflate', 'sdch')),
            array("gzip, deflate\t,sdch", array('gzip', 'deflate', 'sdch')),
        );
    }

    /**
     * @dataProvider dataProviderForTestParseAcceptEncodingHeaderWithQualities
     */
    public function testParseAcceptEncodingHeaderWithQualities($header, $expected)
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

    public static function dataProviderForTestParseAcceptEncodingHeaderWithQualities()
    {
        return array(
            array('gzip;q=1.0, identity; q=0.5, *;q=0', array('gzip' => 1, 'identity' => 0.5, '*' => 0)),
        );
    }

}
