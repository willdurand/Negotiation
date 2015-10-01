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
        $acceptHeader         = $this->negotiator->getBest($acceptLanguageHeader, array('en', 'fr'));

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    /**
     * @dataProvider dataProviderForGetBest
     */
    public function testGetBest($acceptLanguageHeader, $expected)
    {
        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader);

        if (null === $expected) {
            $this->assertNull($acceptHeader);
        } else {
            $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
            $this->assertEquals($expected, $acceptHeader->getValue());
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

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('en-US', $acceptHeader->getValue());
    }

    /**
     * Given a accept header containing specific languages (here 'en-US', 'fr-FR')
     *  And priorities containing a generic version of that language
     * Then the best language is mapped to the generic one here 'fr'
     */
    public function testSpecificLanguageAreMappedToGeneric()
    {
        $acceptLanguageHeader = 'fr-FR, en-US;q=0.8';
        $priorities           = array('fr');

        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    public function testGetBestWithWildcard()
    {
        $acceptLanguageHeader = 'en, *;q=0.9';
        $priorities           = array('fr');

        $acceptHeader = $this->negotiator->getBest($acceptLanguageHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
    }

    public function testGetBestDoesNotMatchPriorities()
    {
        $acceptLanguageHeader = 'en, de';
        $priorities           = array('fr');

        $this->assertNull($this->negotiator->getBest($acceptLanguageHeader, $priorities));
    }

    public static function dataProviderForGetBest()
    {
        return array(
            array('da, en-gb;q=0.8, en;q=0.7', 'da'),
            array('da, en-gb;q=0.8, en;q=0.7, *', 'da'),
            array('es-ES;q=0.7, es; q=0.6 ,fr; q=1.0, en; q=0.5,dk , fr-CH', 'fr-CH'),
            array('fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4', 'fr-FR'),
            array('', null),
            array(null, null),
        );
    }
}
