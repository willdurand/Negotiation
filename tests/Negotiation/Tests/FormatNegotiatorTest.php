<?php

namespace Negotiation\Tests;

use Negotiation\FormatNegotiator;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FormatNegotiatorTest extends TestCase
{

    /**
     * @var FormatNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new FormatNegotiator();
    }

    /**
     * @dataProvider dataProviderForGetBest
     */
    public function testGetBest($acceptHeader, $priorities, $expected)
    {
        $acceptHeader = $this->negotiator->getBest($acceptHeader, $priorities);

        if (null === $expected) {
            $this->assertNull($acceptHeader);
        } else {
            $this->assertNotNull($acceptHeader);
            if (is_array($expected)) {
                $this->assertEquals($expected['value'], $acceptHeader->getValue());
                $this->assertEquals($expected['quality'], $acceptHeader->getQuality());

                if (isset($expected['parameters'])) {
                    foreach ($expected['parameters'] as $key => $value) {
                        $this->assertTrue($acceptHeader->hasParameter($key));
                        $this->assertEquals($value, $acceptHeader->getParameter($key));
                    }

                    $this->assertCount(count($expected['parameters']), $acceptHeader->getParameters());
                }
            } else {
                $this->assertEquals($expected, $acceptHeader->getValue());
            }
        }
    }

    /**
     * @dataProvider dataProviderForGetBestFormat
     */
    public function testGetBestFormat($acceptHeader, $priorities, $expected)
    {
        $bestFormat = $this->negotiator->getBestFormat($acceptHeader, $priorities);

        $this->assertEquals($expected, $bestFormat);
    }

    public static function dataProviderForGetBest()
    {
        $pearAcceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,text/*;q=0.7,*/*,image/gif; q=0.8, image/jpeg; q=0.6, image/*';

        return array(
            // PEAR HTTP2 tests
            array(
                $pearAcceptHeader,
                array(
                    'image/gif',
                    'image/png',
                    'application/xhtml+xml',
                    'application/xml',
                    'text/html',
                    'image/jpeg',
                    'text/plain',
                ),
                'text/html'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'image/gif',
                    'image/png',
                    'application/xhtml+xml',
                    'application/xml',
                    'image/jpeg',
                    'text/plain',
                ),
                'application/xhtml+xml'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'image/gif',
                    'image/png',
                    'application/xml',
                    'image/jpeg',
                    'text/plain',
                ),
                'application/xml'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'image/gif',
                    'image/png',
                    'image/jpeg',
                    'text/plain',
                ),
                'image/gif'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'image/png',
                    'image/jpeg',
                    'text/plain',
                ),
                'text/plain'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'image/png',
                    'image/jpeg',
                ),
                'image/jpeg'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'image/png',
                ),
                'image/png'
            ),
            array(
                $pearAcceptHeader,
                array(
                    'audio/midi',
                ),
                'audio/midi'
            ),
            array(
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                array(
                    'application/rss+xml',
                    '*/*',
                ),
                'application/rss+xml'
            ),
            // See: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(),
                array(
                    'value'   => 'text/html;level=1',
                    'quality' => 1,
                )
            ),
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(
                    'text/html'
                ),
                array(
                    'value'   => 'text/html',
                    'quality' => 0.7,
                )
            ),
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(
                    'text/plain'
                ),
                array(
                    'value'   => 'text/plain',
                    'quality' => 0.3,
                )
            ),
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(
                    'image/jpeg',
                ),
                array(
                    'value'   => 'image/jpeg',
                    'quality' => 0.5,
                )
            ),
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(
                    'text/html;level=2'
                ),
                array(
                    'value'   => 'text/html;level=2',
                    'quality' => 0.4,
                )
            ),
            array(
                'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
                array(
                    'text/html;level=3'
                ),
                array(
                    'value'      => 'text/html;level=3',
                    'quality'    => 0.7,
                    'parameters' => array(
                        'level' => 3,
                    ),
                )
            ),
            // LWS / case sensitivity
            array(
                'text/* ; q=0.3, text/html ;Q=0.7, text/html ; level=1, text/html ;level = 2 ;q=0.4, */* ; q=0.5',
                array(
                    'text/html; level=2'
                ),
                array(
                    'value'      => 'text/html;level=2',
                    'quality'    => 0.4,
                    'parameters' => array(
                        'level' => 2,
                    ),
                )
            ),
            array(
                'text/* ; q=0.3, text/html;Q=0.7, text/html ;level=1, text/html; level=2;q=0.4, */*;q=0.5',
                array(
                    'text/html; level=3'
                ),
                array(
                    'value'      => 'text/html;level=3',
                    'quality'    => 0.7,
                    'parameters' => array(
                        'level' => 3,
                    ),
                )
            ),
            array(
                '*/*',
                array(),
                array(
                    'value'      => '*/*',
                    'quality'    => 0.01,
                    'parameters' => array(),
                ),
            ),
            // Incompatible
            array(
                'text/html',
                array(
                    'application/rss'
                ),
                null
            ),
            array(
                'text/rdf+n3; q=0.8, application/rdf+json; q=0.8, text/turtle; q=1.0, text/n3; q=0.8, application/ld+json; q=0.5, application/rdf+xml; q=0.8',
                array(),
                'text/turtle'
            ),
            array(
                'application/rdf+xml;q=0.5,text/html;q=.3',
                array(),
                'application/rdf+xml'
            ),
            array(
                'application/xhtml+xml;q=0.5',
                array(),
                'application/xhtml+xml'
            ),
            array(
                'application/rdf+xml;q=0.5,text/html;q=.5',
                array(),
                'application/rdf+xml'
            ),
            array(
                'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c',
                array(),
                'text/html',
            ),
            // IE8 Accept header
            array(
                'image/jpeg, application/x-ms-application, image/gif, application/xaml+xml, image/pjpeg, application/x-ms-xbap, */*',
                array(
                    'text/html',
                    'application/xhtml+xml',
                    '*/*'
                ),
                'text/html',
            ),
        );
    }

    public static function dataProviderForGetBestFormat()
    {
        return array(
            array(null, array('html', 'json', '*/*'), null),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array(), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('html', 'json', '*/*'), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('html', 'json', '*/*'), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('rss', '*/*'), 'rss'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('xml'), 'xml'),
            // This shows clearly that the acceptheader is leading over server priorities
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('xml', 'html'), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('json', 'xml'), 'xml'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('json'), 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*', array('json'), 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*', array('json'), 'json'),
            array('text/html,application/xhtml+xml,application/xml', array('json'), null),
            array('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c', array('*/*'), 'html'),
            array('text/html, application/json;q=0.8, text/csv;q=0.7', array(), 'html'),
            array('text/html', array('text/xml'), null),
            array('text/*, text/html, text/html;level=1, */*', array(), 'html'),
            array('text/html; q=0.0', array(), null),
        );
    }

    public function testGetFormat()
    {
        $this->assertEquals('html', $this->negotiator->getFormat('application/xhtml+xml'));
    }

    public function testGetFormatReturnsNullIfNotFound()
    {
        $this->assertNull($this->negotiator->getFormat('foo'));
    }

    public function testRegisterFormat()
    {
        $format   = 'foo';
        $mimeType = 'foo/bar';

        $this->negotiator->registerFormat($format, array($mimeType));
        $this->assertEquals($format, $this->negotiator->getFormat($mimeType));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Format "html" already registered, and override was set to "false".
     */
    public function testRegisterFormatWithExistingFormat()
    {
        $this->negotiator->registerFormat('html', array());
    }

    /**
     * @dataProvider dataProviderForNormalizePriorities
     */
    public function testNormalizePriorities($priorities, $expected)
    {
        $priorities = $this->negotiator->normalizePriorities($priorities);

        $this->assertEquals($expected, $priorities);
    }

    public static function dataProviderForNormalizePriorities()
    {
        return array(
            array(
                array('application/json', 'application/xml'),
                array('application/json', 'application/xml')
            ),
            array(
                array('json', 'application/xml', 'text/*', 'rdf', '*/*'),
                array('application/json', 'application/x-json', 'application/xml', 'text/*', 'application/rdf+xml', '*/*')
            ),
            array(
                array('json', 'html', '*/*'),
                array('application/json', 'application/x-json', 'text/html', 'application/xhtml+xml', '*/*')
            ),
        );
    }
}
