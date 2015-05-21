<?php

namespace Negotiation\Tests;

use Negotiation\LanguageNegotiator;

class LanguageNegotiatorTest extends TestCase
{

    /**
     * @var LanguageNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new LanguageNegotiator();
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
        $acceptLanguageHeader = 'en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2';
        $acceptHeader         = $this->negotiator->getBest($acceptLanguageHeader);

        $this->assertInstanceOf('Negotiation\AcceptLanguageHeader', $acceptHeader);
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
        $acceptHeader         = $this->negotiator->getBest($acceptLanguageHeader, array('en', 'fr'));

        $this->assertInstanceOf('Negotiation\AcceptLanguageHeader', $acceptHeader);
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
    public function testGenericLanguageAreMappedToSpecific()
    {
        $acceptLanguageHeader = 'fr-FR, en;q=0.8';
        $priorities           = array('en-US', 'de-DE');

        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptLanguageHeader', $acceptHeader);
        $this->assertEquals('en-US', $acceptHeader->getValue());
    }

    public function testGetBestWithWildcard()
    {
        $acceptLanguageHeader = 'en, *;q=0.9';
        $priorities           = array('fr');

        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptLanguageHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    public function testGetBestRespectsPriorities()
    {
        $acceptHeader = $this->negotiator->getBest('foo, bar, yo', array('yo'));

        $this->assertInstanceOf('Negotiation\AcceptLanguageHeader', $acceptHeader);
        $this->assertEquals('yo', $acceptHeader->getValue());
    }

    public function testGetBestDoesNotMatchPriorities()
    {
        $acceptLanguageHeader = 'en, de';
        $priorities           = array('fr');

        $this->assertNull($this->negotiator->getBest($acceptLanguageHeader, $priorities));
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
}
