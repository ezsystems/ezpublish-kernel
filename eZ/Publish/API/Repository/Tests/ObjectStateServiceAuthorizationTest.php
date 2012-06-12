<?php
/**
 * File containing the ObjectStateServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;

/**
 * Test case for operations in the ObjectStateService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ObjectStateService
 */
class ObjectStateServiceAuthorizationTest extends \eZ\Publish\API\Repository\Tests\BaseTest
{
    /**
     * Test for the createObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateObjectStateGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::createObjectStateGroup() is not implemented." );
    }

    /**
     * Test for the updateObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateObjectStateGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::updateObjectStateGroup() is not implemented." );
    }

    /**
     * Test for the deleteObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteObjectStateGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::deleteObjectStateGroup() is not implemented." );
    }

    /**
     * Test for the createObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateObjectStateThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::createObjectState() is not implemented." );
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateObjectStateThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::updateObjectState() is not implemented." );
    }

    /**
     * Test for the setPriorityOfObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setPriorityOfObjectState()
     *
     */
    public function testSetPriorityOfObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setPriorityOfObjectState() is not implemented." );
    }

    /**
     * Test for the deleteObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectState()
     *
     */
    public function testDeleteObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::deleteObjectState() is not implemented." );
    }

    /**
     * Test for the setObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSetObjectStateThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setObjectState() is not implemented." );
    }
}
