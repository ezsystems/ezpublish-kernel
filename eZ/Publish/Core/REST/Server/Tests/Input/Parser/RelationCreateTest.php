<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\RelationCreate;

class RelationCreateTest extends BaseTest
{
    /**
     * Tests the RelationCreate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'Destination' => array(
                '_href' => '/content/objects/42'
            ),
        );

        $relationCreate = $this->getRelationCreate();
        $result = $relationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertEquals(
            42,
            $result,
            'RelationCreate struct not parsed correctly.'
        );
    }

    /**
     * Test RelationCreate parser throwing exception on missing Destination
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'Destination' element for RelationCreate.
     */
    public function testParseExceptionOnMissingDestination()
    {
        $inputArray = array();

        $relationCreate = $this->getRelationCreate();
        $relationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test RelationCreate parser throwing exception on missing Destination href
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Destination element in RelationCreate.
     */
    public function testParseExceptionOnMissingDestinationHref()
    {
        $inputArray = array(
            'Destination' => array()
        );

        $relationCreate = $this->getRelationCreate();
        $relationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the RelationCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\RelationCreate
     */
    protected function getRelationCreate()
    {
        return new RelationCreate( $this->getUrlHandler() );
    }
}
