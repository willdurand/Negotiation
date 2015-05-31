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

    public function testGetBestWithWildcard()
    {
        $acceptCharsetHeader = 'en, *;q=0.9';
        $priorities           = array('fr');

        $acceptHeader = $this->negotiator->getBest($acceptCharsetHeader, $priorities);

        $this->assertInstanceOf('Negotiation\AcceptCharsetHeader', $acceptHeader);
        $this->assertEquals('fr', $acceptHeader->getValue());
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
}
