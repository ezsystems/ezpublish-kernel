<?php
/**
 * File containing a LocationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values\Content\Location;

class LocationTest extends BaseTest
{
    /**
     * Tests the location parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function testParse()
    {
        $locationParser = $this->getParser();

        $inputArray = array(
            '_href' => '/content/locations/1/2/21/42',
            'priority' => '0',
            'hidden' => 'false',
            'invisible' => 'false',
            'remoteId' => 'remote-id',
            'ParentLocation' => array(
                '_href' => '/content/locations/1/2/21'
            ),
            'pathString' => '/1/2/21/42',
            'depth' => '3',
            'Content' => array(
                '_href' => '/content/objects/42'
            ),
            'sortField' => 'PATH',
            'sortOrder' => 'ASC',
        );

        $result = $locationParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the resulting location is in fact an instance of Location class
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultIsLocation( $result )
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $result
        );
    }

    /**
     * Tests that the resulting location contains the ID
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsId( $result )
    {
        $this->assertEquals(
            '/content/locations/1/2/21/42',
            $result->id
        );
    }

    /**
     * Tests that the resulting location contains the priority
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsPriority( $result )
    {
        $this->assertEquals(
            0,
            $result->priority
        );
    }

    /**
     * Tests that the resulting location contains hidden property
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsHidden( $result )
    {
        $this->assertEquals(
            false,
            $result->hidden
        );
    }

    /**
     * Tests that the resulting location contains invisible property
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsInvisible( $result )
    {
        $this->assertEquals(
            false,
            $result->invisible
        );
    }

    /**
     * Tests that the resulting location contains remote ID
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsRemoteId( $result )
    {
        $this->assertEquals(
            'remote-id',
            $result->remoteId
        );
    }

    /**
     * Tests that the resulting location contains parent location ID
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsParentLocationId( $result )
    {
        $this->assertEquals(
            '/content/locations/1/2/21',
            $result->parentLocationId
        );
    }

    /**
     * Tests that the resulting location contains path string
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsPathString( $result )
    {
        $this->assertEquals(
            '/1/2/21/42',
            $result->pathString
        );
    }

    /**
     * Tests that the resulting location contains depth
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsDepth( $result )
    {
        $this->assertEquals(
            3,
            $result->depth
        );
    }

    /**
     * Tests that the resulting location contains sort field
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsSortField( $result )
    {
        $this->assertEquals(
            Location::SORT_FIELD_PATH,
            $result->sortField
        );
    }

    /**
     * Tests that the resulting location contains sort order
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $result
     * @depends testParse
     */
    public function testResultContainsSortOrder( $result )
    {
        $this->assertEquals(
            Location::SORT_ORDER_ASC,
            $result->sortOrder
        );
    }

    /**
     * Gets the parser for location
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Location;
     */
    protected function getParser()
    {
        return new Parser\Location( new ParserTools() );
    }
}
