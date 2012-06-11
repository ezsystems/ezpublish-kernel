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
class ObjectStateServiceTest extends \eZ\Publish\API\Repository\Tests\BaseTest
{
    /**
     * Test for the newObjectStateGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupCreateStruct()
     *
     */
    public function testNewObjectStateGroupCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            'publishing'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupCreateStruct',
            $objectStateGroupCreate
        );
        return $objectStateGroupCreate;
    }

    /**
     * testNewObjectStateGroupCreateStructValues
     *
     * @param ObjectStateGroupCreateStruct $objectStateGroupCreate
     * @return void
     * @depends testNewObjectStateGroupCreateStruct
     */
    public function testNewObjectStateGroupCreateStructValues( ObjectStateGroupCreateStruct $objectStateGroupCreate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => 'publishing',
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateGroupCreate
        );
    }

    /**
     * Test for the newObjectStateGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupUpdateStruct()
     *
     */
    public function testNewObjectStateGroupUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupUpdate = $objectStateService->newObjectStateGroupUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupUpdateStruct',
            $objectStateGroupUpdate
        );
        return $objectStateGroupUpdate;
    }

    /**
     * testNewObjectStateGroupUpdateStructValues
     *
     * @param ObjectStateGroupUpdateStruct $objectStateGroupUpdate
     * @return void
     * @depends testNewObjectStateGroupUpdateStruct
     */
    public function testNewObjectStateGroupUpdateStructValues( ObjectStateGroupUpdateStruct $objectStateGroupUpdate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => null,
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateGroupUpdate
        );
    }

    /**
     * Test for the newObjectStateCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateCreateStruct()
     *
     */
    public function testNewObjectStateCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateCreate = $objectStateService->newObjectStateCreateStruct(
            'pending'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateCreateStruct',
            $objectStateCreate
        );
        return $objectStateCreate;
    }

    /**
     * testNewObjectStateCreateStructValues
     *
     * @param ObjectStateCreateStruct $objectStateCreate
     * @return void
     * @depends testNewObjectStateCreateStruct
     */
    public function testNewObjectStateCreateStructValues( ObjectStateCreateStruct $objectStateCreate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => 'pending',
                'priority'            => false,
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateCreate
        );
    }

    /**
     * Test for the newObjectStateUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateUpdateStruct()
     *
     */
    public function testNewObjectStateUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateUpdate = $objectStateService->newObjectStateUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateUpdateStruct',
            $objectStateUpdate
        );
        return $objectStateUpdate;
    }

    /**
     * testNewObjectStateUpdateStructValues
     *
     * @param ObjectStateUpdateStruct $objectStateUpdate
     * @return void
     * @depends testNewObjectStateUpdateStruct
     */
    public function testNewObjectStateUpdateStructValues( ObjectStateUpdateStruct $objectStateUpdate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => null,
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateUpdate
        );
    }

    /**
     * Test for the createObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     *
     */
    public function testCreateObjectStateGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            'publishing'
        );
        $objectStateGroupCreate->defaultLanguageCode = 'eng-US';
        $objectStateGroupCreate->names = array(
            'eng-US' => 'Publishing',
            'eng-GB' => 'Sindelfingen',
        );
        $objectStateGroupCreate->descriptions = array(
            'eng-US' => 'Put something online',
            'eng-GB' => 'Put something ton Sindelfingen.',
        );

        $createdObjectStateGroup = $objectStateService->createObjectStateGroup(
            $objectStateGroupCreate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $createdObjectStateGroup
        );
        return $createdObjectStateGroup;
    }

    /**
     * testCreateObjectStateGroupStructValues
     *
     * @param ObjectStateGroup $createdObjectStateGroup
     * @return void
     * @depends testCreateObjectStateGroup
     */
    public function testCreateObjectStateGroupStructValues( ObjectStateGroup $createdObjectStateGroup )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => 'publishing',
                'defaultLanguageCode' => 'eng-US',
                'languageCodes'       => array( 'eng-US', 'eng-GB' ),
                'names'               => array(
                    'eng-US' => 'Publishing',
                    'eng-GB' => 'Sindelfingen',
                ),
                'descriptions'        => array(
                    'eng-US' => 'Put something online',
                    'eng-GB' => 'Put something ton Sindelfingen.',
                ),
            ),
            $createdObjectStateGroup
        );
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup()
     *
     */
    public function testLoadObjectStateGroup()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $loadedObjectStateGroup
        );
        return $loadedObjectStateGroup;
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadObjectStateGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentObjectStateGroupId = $this->generateId( 'objectstategroup', PHP_INT_MAX );
        /* BEGIN: Use Case */
        // $nonExistentObjectStateGroupId contains an ID for an object state
        // that does not exist
        $objectStateService = $repository->getObjectStateService();

        // Throws a not found exception
        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $nonExistentObjectStateGroupId
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups()
     *
     */
    public function testLoadObjectStateGroups()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectStateGroups() is not implemented." );
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset)
     *
     */
    public function testLoadObjectStateGroupsWithFirstParameter()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectStateGroups() is not implemented." );
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset, $limit)
     *
     */
    public function testLoadObjectStateGroupsWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectStateGroups() is not implemented." );
    }

    /**
     * Test for the loadObjectStates() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStates()
     *
     */
    public function testLoadObjectStates()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectStates() is not implemented." );
    }

    /**
     * Test for the updateObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup()
     *
     */
    public function testUpdateObjectStateGroup()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::updateObjectStateGroup() is not implemented." );
    }

    /**
     * Test for the deleteObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup()
     *
     */
    public function testDeleteObjectStateGroup()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::deleteObjectStateGroup() is not implemented." );
    }

    /**
     * Test for the createObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     *
     */
    public function testCreateObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::createObjectState() is not implemented." );
    }

    /**
     * Test for the loadObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     *
     */
    public function testLoadObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectState() is not implemented." );
    }

    /**
     * Test for the loadObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadObjectStateThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectState() is not implemented." );
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     *
     */
    public function testUpdateObjectState()
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
     *
     */
    public function testSetObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setObjectState() is not implemented." );
    }

    /**
     * Test for the setObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentExceptioon
     */
    public function testSetObjectStateThrowsInvalidArgumentExceptioon()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setObjectState() is not implemented." );
    }

    /**
     * Test for the getObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::getObjectState()
     *
     */
    public function testGetObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::getObjectState() is not implemented." );
    }

    /**
     * Test for the getContentCount() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::getContentCount()
     *
     */
    public function testGetContentCount()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::getContentCount() is not implemented." );
    }

}
