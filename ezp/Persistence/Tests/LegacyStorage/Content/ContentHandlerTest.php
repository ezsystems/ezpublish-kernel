<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content,
    ezp\Persistence\LegacyStorage\Content\ContentHandler;

/**
 * Test case for ContentHandler
 */
class ContentHandlerTest extends TestCase
{
    public function testCtor()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Mapper'
        );
        $storageRegistryMock = $this->getMockForAbstractClass(
            'ezp\Persistence\LegacyStorage\Content\StorageRegistry'
        );

        $handler = new ContentHandler(
            $gatewayMock,
            $mapperMock,
            $storageRegistryMock
        );

        $this->assertAttributeSame(
            $gatewayMock,
            'contentGateway',
            $handler
        );
        $this->assertAttributeSame(
            $mapperMock,
            'mapper',
            $handler
        );
        $this->assertAttributeSame(
            $storageRegistryMock,
            'storageRegistry',
            $handler
        );
    }

    /**
     * Returns a mock object for the ContentGateway.
     *
     * @return ContentGateway
     */
    protected function getGatewayMock()
    {
        return $this->getMockForAbstractClass(
            'ezp\Persistence\LegacyStorage\Content\ContentGateway'
        );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
