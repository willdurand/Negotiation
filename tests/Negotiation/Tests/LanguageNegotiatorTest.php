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
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($acceptHeader, $priorities, $expected)
    {
        try {
            $acceptHeader = $this->negotiator->getBest($acceptHeader, $priorities);

            if (null === $acceptHeader) {
                $this->assertNull($expected);
            } else {
                $this->assertInstanceOf('\Negotiation\AcceptLanguageHeader', $acceptHeader);
                $this->assertEquals($expected, $acceptHeader->getValue());
            }
        } catch (\Exception $e) {
            $this->assertSame($expected, $e->getMessage());
        }
    }

    public static function dataProviderForTestGetBest()
    {
        return array(
            array('en, de', array('fr'), null),
            array('foo, bar, yo', array('baz', 'biz'), null),
            array('fr-FR, en;q=0.8', array('en-US', 'de-DE'), 'en-US'),
            array('en, *;q=0.9', array('fr'), 'fr'),
            array('foo, bar, yo', array('yo'), 'yo'),
            array('en; q=0.1, fr; q=0.4, bu; q=1.0', array('en', 'fr'), 'fr'),
            array('en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2', array('en', 'fu'), 'fu'),
            array('', array('en', 'fu'), 'empty header given'),
        );

    }

    /**
     * @dataProvider dataProviderForTestParseHeader
     */
    public function testParseHeader($header, $expected)
    {
        $accepts = $this->call_private_method('\Negotiation\Negotiator', 'parseHeader', $this->negotiator, array($header));

        $this->assertSame($expected, $accepts);
    }

    public static function dataProviderForTestParseHeader()
    {
        return array(
            array('en; q=0.1, fr; q=0.4, bu; q=1.0', array('en; q=0.1', 'fr; q=0.4', 'bu; q=1.0')),
            array('en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2', array('en; q=0.1', 'fr; q=0.4', 'fu; q=0.9', 'de; q=0.2')),
        );
    }

}
