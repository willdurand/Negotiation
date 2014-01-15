<?php

namespace Negotiation\Tests;

use Negotiation\ApiFormatNegotiator;

/**
 * @author Patrick van Kouteren <p.vankouteren@wedesignit.nl>
 */
class ApiFormatNegotiatorTest extends TestCase
{
    /**
     * @var ApiFormatNegotiator
     */
    private $negotiator;

    protected function setUp()
    {
        $this->negotiator = new ApiFormatNegotiator();
    }

    /**
     * @dataProvider dataProviderForSanitize
     */
    /*public function testSanitize($values, $expected)
    {
        $sane = $this->negotiator->sanitize($values);
        $this->assertEquals($expected, $sane);
    }
    */

    public static function dataProviderForSanitize()
    {
        return array(
            array(
                // values
                array(
                    'application/json' => array('1'),
                    'application/xml' => array('2'),
                ),
                // expected
                array(
                    'application/json' => array('1'),
                    'application/xml' => array('2'),
                ),
            ),
            array(
                // values
                array(
                    'application/json' => array('1', '2', '3'),
                    'application/xml' => array('2', '2.1'),
                ),
                // expected
                array(
                    'application/json' => array('1', '2', '3'),
                    'application/xml' => array('2', '2.1'),
                ),
            ),
            array(
                // values
                array(
                    'application/json' => array('*'),
                    'application/xml' => array('*'),
                ),
                // expected
                array(
                    'application/json' => array('*'),
                    'application/xml' => array('*'),
                ),
            ),
            array(
                // values
                array(
                    'application/json',
                    'application/xml',
                ),
                // expected
                array(
                    'application/json' => array('*'),
                    'application/xml' => array('*'),
                ),
            ),
            array(
                // values
                array(
                    'text/xml' => array(2),
                    'application/x-json' => array(1, 2),
                ),
                // expected
                array(
                    'text/xml' => array(2),
                    'application/x-json' => array(1, 2),
                ),
            )
        );
    }

    /**
     * @dataProvider dataProviderForGetBest
     */
    public function testGetBest($acceptHeader1, $priorities, $expected)
    {
        $acceptHeader = $this->negotiator->getBest($acceptHeader1, $priorities);

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


    public static function dataProviderForGetBest()
    {
        return array(
            array(
                'application/xml;version=1,application/json;version=2',
                array('application/json', 'application/xml'),
                array(
                    'value' => 'application/xml',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 1,
                    )
                )
            ),
            array(
                'application/xml;version=1,application/json;version=2',
                array('application/json' => array('1', '2'), 'application/xml' => array('2')),
                array(
                    'value' => 'application/json',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 2,
                    )
                )
            ),
            array(
                'application/xml;version=1,application/json;version=1',
                array('application/xml' => array('2'), 'application/json' => array('1', '2')),
                array(
                    'value' => 'application/json',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 1,
                    )

                )
            ),
            // Client header: prefer newer versions over older
            array(
                'application/json;version=1.2,application/json;version=1.1,application/xml;version=1.0',
                array('application/json' => array('1.0', '1.2', '1.3', '2.0'), 'application/xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')),
                array(
                    'value' => 'application/json',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 1.2,
                    )
                )
            ),
            // Client header: prefer older versions over newer
            array(
                'application/json;version=1.0,application/json;version=1.1,application/xml;version=1.0',
                array('application/json' => array('1.0', '1.2', '1.3', '2.0'), 'application/xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')),
                array(
                    'value' => 'application/json',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 1.0,
                    )
                )
            ),
            // Client wants to use version 1.0, no matter the response format
            array(
                'application/json;version=1.0,text/xml;version=1.0',
                array(
                    'application/json' => array('1.0', '1.2', '1.3', '2.0'),
                    'application/xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')
                ),
                array(
                    'value' => 'application/json',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 1.0,
                    )
                )
            ),
            // Client wants to use version 1.1, no matter the response format
            array('application/json;version=1.1,text/xml;version=1.1',
                array('application/json' => array('1.0', '1.2', '1.3', '2.0'),
                    'text/xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')
                ),
                array(
                    'value' => 'text/xml',
                    'quality' => 1,
                    'parameters' => array(
                        'version' => 1.1,
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider dataProviderForGetBestFormat
     */
    public function testGetBestFormat($acceptHeader, $priorities, $expected)
    {
        $bestFormat = $this->negotiator->getBestFormat($acceptHeader, $priorities);

        $this->assertEquals($expected, $bestFormat);

    }

    public static function dataProviderForGetBestFormat()
    {
        return array(
            // No versions defined, equal to wildcard. Pick the API preferred one as the client can handle both.
            array('application/xml;version=1,application/json;version=2', array('json', 'xml'), 'xml'),
            // API prefers JSON v2, client accepts this.
            array('application/xml;version=1,application/json;version=2', array('json' => array('1', '2'), 'xml' => array('2')), 'json'),
            // XML v2 is supported, but client can only handle v1 for XML and JSON.
            array('application/xml;version=1,application/json;version=1', array('xml' => array('2'), 'json' => array('1', '2')), 'json'),
            // Client can only handle various JSON formats and versions
            array('application/json;version=1,application/x-json;version=2', array('xml' => array('1', '1.1'), 'json' => array('1', '1.1')), 'json'),
            // Client can only handle JSON, but API doesn't support these versions
            array('application/json;version=1,application/x-json;version=2', array('xml' => array('1.2', '1.3', '1.4', '1.5'), 'json' => array('1.2', '1.3', '1.4', '1.5')), null),
            // Use case: a bug was found in version 1.1 for the JSON format. If client requests this version: switch to XML.
            array('application/json;version=1.0,text/xml;version=1.0', array('json' => array('1.0', '1.2', '1.3', '2.0'), 'xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')), 'json'),
            array('application/json;version=1.1,text/xml;version=1.1', array('json' => array('1.0', '1.2', '1.3', '2.0'), 'xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')), 'xml'),
            array('application/json;version=1.2,application/json;version=1.1,application/xml;version=1.0', array('json' => array('1.0', '1.2', '1.3', '2.0'), 'xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')), 'json'),
        );
    }

}
