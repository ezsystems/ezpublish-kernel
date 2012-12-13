<?php
/**
 * File containing the ObjectStateServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the ObjectStateService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ObjectStateService
 * @group integration
 * @group authorization
 */
class ObjectStateServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testCreateObjectStateGroup
     */
    public function testCreateObjectStateGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

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

        // Throws unauthorized exception, since the anonymous user must not
        // create object state groups
        $createdObjectStateGroup = $objectStateService->createObjectStateGroup(
            $objectStateGroupCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testUpdateObjectStateGroup
     */
    public function testUpdateObjectStateGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->identifier = 'sindelfingen';
        $groupUpdateStruct->defaultLanguageCode = 'ger-DE';
        $groupUpdateStruct->names = array(
            'ger-DE' => 'Sindelfingen',
        );
        $groupUpdateStruct->descriptions = array(
            'ger-DE' => 'Sindelfingen ist nicht nur eine Stadt'
        );

        // Throws unauthorized exception, since the anonymous user must not
        // update object state groups
        $updatedObjectStateGroup = $objectStateService->updateObjectStateGroup(
            $loadedObjectStateGroup,
            $groupUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testDeleteObjectStateGroup
     */
    public function testDeleteObjectStateGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        // Throws unauthorized exception, since the anonymous user must not
        // delete object state groups
        $objectStateService->deleteObjectStateGroup( $loadedObjectStateGroup );
        /* END: Use Case */
    }

    /**
     * Test for the createObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testCreateObjectState
     */
    public function testCreateObjectStateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            'locked_and_unlocked'
        );
        $objectStateCreateStruct->priority = 23;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = array(
            'eng-US' => 'Locked and Unlocked',
        );
        $objectStateCreateStruct->descriptions = array(
            'eng-US' => 'A state between locked and unlocked.',
        );

        // Throws unauthorized exception, since the anonymous user must not
        // create object states
        $createdObjectState = $objectStateService->createObjectState(
            $loadedObjectStateGroup,
            $objectStateCreateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testUpdateObjectState
     */
    public function testUpdateObjectStateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        $updateStateStruct = $objectStateService->newObjectStateUpdateStruct();
        $updateStateStruct->identifier = 'somehow_locked';
        $updateStateStruct->defaultLanguageCode = 'ger-DE';
        $updateStateStruct->names = array(
            'eng-US' => 'Somehow locked',
            'ger-DE' => 'Irgendwie gelockt',
        );
        $updateStateStruct->descriptions = array(
            'eng-US' => 'The object is somehow locked',
            'ger-DE' => 'Sindelfingen',
        );

        // Throws unauthorized exception, since the anonymous user must not
        // update object states
        $updatedObjectState = $objectStateService->updateObjectState(
            $loadedObjectState,
            $updateStateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the setPriorityOfObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setPriorityOfObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testSetPriorityOfObjectState
     */
    public function testSetPriorityOfObjectStateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $initiallyLoadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        // Throws unauthorized exception, since the anonymous user must not
        // set priorities for object states
        $objectStateService->setPriorityOfObjectState(
            $initiallyLoadedObjectState,
            23
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testDeleteObjectState
     */
    public function testDeleteObjectStateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $notLockedObjectStateId = $this->generateId( 'objectstate', 1 );
        $lockedObjectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $notLockedObjectStateId is the ID of the state "not_locked"
        $objectStateService = $repository->getObjectStateService();

        $notLockedObjectState = $objectStateService->loadObjectState( $notLockedObjectStateId );

        // Throws unauthorized exception, since the anonymous user must not
        // delete object states
        $objectStateService->deleteObjectState( $notLockedObjectState );
        /* END: Use Case */
    }

    /**
     * Test for the setContentState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setContentState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testSetContentState
     */
    public function testSetContentStateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        $ezLockObjectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        $lockedObjectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // Set anonymous user
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // $anonymousUserId is the content ID of "Anonymous User"
        // $ezLockObjectStateGroupId contains the ID of the "ez_lock" object
        // state group
        // $lockedObjectStateId is the ID of the state "locked"
        $contentService     = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $ezLockObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $ezLockObjectStateGroupId
        );
        $lockedObjectState = $objectStateService->loadObjectState( $lockedObjectStateId );

        // Throws unauthorized exception, since the anonymous user must not
        // set object state
        $objectStateService->setContentState(
            $contentInfo,
            $ezLockObjectStateGroup,
            $lockedObjectState
        );
        /* END: Use Case */
    }
}
