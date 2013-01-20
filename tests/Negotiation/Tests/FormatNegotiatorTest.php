<?php

namespace Negotiation\Tests;

use Negotiation\FormatNegotiator;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FormatNegotiatorTest extends TestCase
{
    protected function setUp()
    {
        $this->negotiator = new FormatNegotiator();
    }

    /**
     * @dataProvider dataProviderForGetBestFormat
     */
    public function testGetBest($acceptHeader, $priorities, $expected)
    {
        $result = $this->negotiator->getBest($acceptHeader, $priorities);

        $this->assertEquals($expected, $result);
    }

    public static function dataProviderForGetBestFormat()
    {
        return array(
            array(null, array('html', 'json', '*/*'), null),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array(), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('html', 'json', '*/*'), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('html', 'json', '*/*'), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('rss', '*/*'), 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('xml'), 'xml'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('json', 'xml'), 'xml'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array('json'), 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*', array('json'), 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*', array('json'), 'json'),
            array('text/html,application/xhtml+xml,application/xml', array('json'), null),
            array('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c', array('*/*'), 'html'),
            array('text/html, application/json;q=0.8, text/csv;q=0.7', array(), 'html'),
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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Format "html" already registered, and override was set to "false".
     */
    public function testRegisterFormatWithExistingFormat()
    {
        $this->negotiator->registerFormat('html', array());
    }
}
