<?php

namespace Negotiation\Tests;

use Negotiation\AcceptHeader;

class AcceptHeaderTest extends TestCase
{

    /**
     * @var AcceptHeader
     */
    private $acceptHeader;

    protected function setUp()
    {
        $this->acceptHeader = new AcceptHeader('foo', 1.0, array(
            'hello' => 'world',
        ));
    }

    public function testGetParameter()
    {
        $this->assertTrue($this->acceptHeader->hasParameter('hello'));
        $this->assertEquals('world', $this->acceptHeader->getParameter('hello'));

        $this->assertFalse($this->acceptHeader->hasParameter('unknown'));
        $this->assertNull($this->acceptHeader->getParameter('unknown'));
        $this->assertFalse($this->acceptHeader->getParameter('unknown', false));
    }

    /**
     * @dataProvider dataProviderForTestIsMediaRange
     */
    public function testIsMediaRange($value, $expected)
    {
        $header = new AcceptHeader($value, 1.0);

        $this->assertEquals($expected, $header->isMediaRange());
    }

    public static function dataProviderForTestIsMediaRange()
    {
        return array(
            array('text/*', true),
            array('*/*', true),
            array('application/json', false),
        );
    }
}
