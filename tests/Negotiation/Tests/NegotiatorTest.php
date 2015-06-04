<?php

namespace Negotiation\Tests;

use Negotiation\Negotiator;
use Negotiation\AcceptHeader;
use Negotiation\Match;

class NegotiatorTest extends TestCase
{

    /**
     * @var Negotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new Negotiator();
    }

    /**
     * @dataProvider dataProviderForTestGetBest
     */
    public function testGetBest($header, $priorities, $expected)
    {
        if (is_string($expected))
            $this->setExpectedException('\Exception', $expected);

        $acceptHeader = $this->negotiator->getBest($header, $priorities);

        if ($acceptHeader === null) {
            $this->assertNull($expected);
        } else {
            $this->assertInstanceOf('Negotiation\AcceptHeader', $acceptHeader);

            $this->assertSame($expected[0], $acceptHeader->getType());
            $this->assertSame($expected[1], $acceptHeader->getParameters());
        }
    }

    public static function dataProviderForTestGetBest()
    {
        $pearAcceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,text/*;q=0.7,*/*,image/gif; q=0.8, image/jpeg; q=0.6, image/*';

        return array(
            array('image/png;q=0.1, text/plain, audio/ogg;q=0.9', array('image/png', 'text/plain', 'audio/ogg'), array('text/plain', array())),
            array('/qwer', array('f/g'), 'invalid media type.'),
            array('image/png, text/plain, audio/ogg', array('baz/asdf'), null),
            array('image/png, text/plain, audio/ogg', array('audio/ogg'), array('audio/ogg', array())),
            array('image/png, text/plain, audio/ogg', array('YO/SuP'), null),
            array('text/html; charset=UTF-8, application/pdf', array('text/html; charset=UTF-8'), array('text/html', array('charset' => 'UTF-8'))),
            array('text/html; charset=UTF-8, application/pdf', array('text/html'), null),
            array('text/html, application/pdf', array('text/html; charset=UTF-8'), array('text/html', array('charset' => 'UTF-8'))),
            // PEAR HTTP2 tests
            array( $pearAcceptHeader, array('image/gif', 'image/png', 'application/xhtml+xml', 'application/xml', 'text/html', 'image/jpeg', 'text/plain',), array('image/gif', array())),
            array( $pearAcceptHeader, array('image/png', 'application/xhtml+xml', 'application/xml', 'image/jpeg', 'text/plain',), array('application/xhtml+xml', array())), # TODO what do we really do here!??!??!?
            array( $pearAcceptHeader, array('image/gif', 'image/png', 'application/xml', 'image/jpeg', 'text/plain',), array('application/xml', array())),
            array( $pearAcceptHeader, array('image/gif', 'image/png', 'image/jpeg', 'text/plain',), array('image/gif', array())),
            array( $pearAcceptHeader, array('image/png', 'image/jpeg', 'text/plain',), array('text/plain', array())),
            array( $pearAcceptHeader, array('image/png', 'image/jpeg',), array('image/jpeg', array())),
            array( $pearAcceptHeader, array('image/png',), array('image/png', array())),
            array( $pearAcceptHeader, array('audio/midi',), array('audio/midi', array())),
            array( 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array( 'application/rss+xml'), array('application/rss+xml', array())),
            // See: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
            array( 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5', array(), array('text/html', array('level' => 1))),
            array( 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5', array( 'text/html'), array('text/html', array())),
            array( 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5', array( 'text/plain'), array('text/plain', array())),
            array( 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5', array( 'image/jpeg',), array('image/jpeg', array())),
            array( 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5', array( 'text/html;level=2'), array('text/html', array('level' => '2'))),
            array( 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5', array( 'text/html;level=3'), array('text/html', array( 'level' => '3'))),
            // LWS / case sensitivity
            array( 'text/* ; q=0.3, text/html ;Q=0.7, text/html ; level=1, text/html ;level = 2 ;q=0.4, */* ; q=0.5', array( 'text/html; level=2'), array('text/html', array( 'level' => '2'))),
            array( 'text/* ; q=0.3, text/html;Q=0.7, text/html ;level=1, text/html; level=2;q=0.4, */*;q=0.5', array( 'text/html; level=3'), array('text/html', array( 'level' => '3'))),
            array( '*/*', array(), array('no priorities given', array()),),
            array( '*/*', array('foo', 'bar', 'baz'), array('foo', array())),
            array( '', array('foo', 'bar', 'baz'), array('empty header given', array())),
            // Incompatible
            array( 'text/html', array( 'application/rss'), null),
            array( 'text/rdf+n3; q=0.8, application/rdf+json; q=0.8, text/turtle; q=1.0, text/n3; q=0.8, application/ld+json; q=0.5, application/rdf+xml; q=0.8', array(), array('text/turtle', array())),
            // IE8 Accept header
            array( 'image/jpeg, application/x-ms-application, image/gif, application/xaml+xml, image/pjpeg, application/x-ms-xbap, */*', array( 'text/html', 'application/xhtml+xml'), array('text/html', array())),
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
            array('text/html ;   q=0.9', array('text/html ;   q=0.9')),
            array('text/html,application/xhtml+xml', array('text/html', 'application/xhtml+xml')),
            array(',,text/html;q=0.8 , , ', array('text/html;q=0.8')),
            array('text/html;charset=utf-8; q=0.8', array('text/html;charset=utf-8; q=0.8')),
            array('text/html; foo="bar"; q=0.8 ', array('text/html; foo="bar"; q=0.8')),
            array('text/html; foo="bar"; qwer="asdf", image/png', array('text/html; foo="bar"; qwer="asdf"', "image/png")),
            array('text/html ; quoted_comma="a,b  ,c,",application/xml;q=0.9,*/*;charset=utf-8; q=0.8', array('text/html ; quoted_comma="a,b  ,c,"', 'application/xml;q=0.9', '*/*;charset=utf-8; q=0.8')),
            array('text/html, application/json;q=0.8, text/csv;q=0.7', array('text/html', 'application/json;q=0.8', 'text/csv;q=0.7'))
        );
    }

    /**
     * @dataProvider dataProviderForTestFindMatches
     */
    public function testFindMatches($headerParts, $priorities, $expected)
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
