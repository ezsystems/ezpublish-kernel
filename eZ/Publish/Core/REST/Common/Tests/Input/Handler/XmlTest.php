<?php

/**
 * File containing the XmlTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Tests\Input\Handler;

use eZ\Publish\Core\REST\Common;
use PHPUnit_Framework_TestCase;

/**
 * Xml input handler test class.
 */
class XmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testConvertInvalidXml()
    {
        $handler = new Common\Input\Handler\Xml();

        $this->assertSame(
            array(
                'text' => 'Hello world!',
            ),
            $handler->convert('{"text":"Hello world!"}')
        );
    }

    public static function getXmlFixtures()
    {
        $fixtures = array();
        foreach (glob(__DIR__ . '/_fixtures/*.xml') as $xmlFile) {
            $fixtures[] = array(
                file_get_contents($xmlFile),
                is_file($xmlFile . '.php') ? include $xmlFile . '.php' : null,
            );
        }

        return $fixtures;
    }

    /**
     * @dataProvider getXmlFixtures
     */
    public function testConvertXml($xml, $expectation)
    {
        $handler = new Common\Input\Handler\Xml();

        $this->assertSame(
            $expectation,
            $handler->convert($xml)
        );
    }
}
