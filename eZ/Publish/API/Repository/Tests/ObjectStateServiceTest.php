<?php

/**
 * File containing the ObjectStateServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for operations in the ObjectStateService using in memory storage.
 *
 * @see \eZ\Publish\API\Repository\ObjectStateService
 * @group object-state
 */
class ObjectStateServiceTest extends BaseTest
{
    /**
     * Test for the newObjectStateGroupCreateStruct() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupCreateStruct()
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
     * testNewObjectStateGroupCreateStructValues.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct $objectStateGroupCreate
     *
     *
     * @depends testNewObjectStateGroupCreateStruct
     */
    public function testNewObjectStateGroupCreateStructValues(ObjectStateGroupCreateStruct $objectStateGroupCreate)
    {
        $this->assertPropertiesCorrect(
            [
                'identifier' => 'publishing',
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null,
            ],
            $objectStateGroupCreate
        );
    }

    /**
     * Test for the newObjectStateGroupUpdateStruct() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupUpdateStruct()
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
     * testNewObjectStateGroupUpdateStructValues.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct $objectStateGroupUpdate
     *
     *
     * @depends testNewObjectStateGroupUpdateStruct
     */
    public function testNewObjectStateGroupUpdateStructValues(ObjectStateGroupUpdateStruct $objectStateGroupUpdate)
    {
        $this->assertPropertiesCorrect(
            [
                'identifier' => null,
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null,
            ],
            $objectStateGroupUpdate
        );
    }

    /**
     * Test for the newObjectStateCreateStruct() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateCreateStruct()
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
     * testNewObjectStateCreateStructValues.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct $objectStateCreate
     *
     *
     * @depends testNewObjectStateCreateStruct
     */
    public function testNewObjectStateCreateStructValues(ObjectStateCreateStruct $objectStateCreate)
    {
        $this->assertPropertiesCorrect(
            [
                'identifier' => 'pending',
                'priority' => false,
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null,
            ],
            $objectStateCreate
        );
    }

    /**
     * Test for the newObjectStateUpdateStruct() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateUpdateStruct()
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
     * testNewObjectStateUpdateStructValues.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdate
     *
     *
     * @depends testNewObjectStateUpdateStruct
     */
    public function testNewObjectStateUpdateStructValues(ObjectStateUpdateStruct $objectStateUpdate)
    {
        $this->assertPropertiesCorrect(
            [
                'identifier' => null,
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null,
            ],
            $objectStateUpdate
        );
    }

    /**
     * Test for the createObjectStateGroup() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     * @depends testNewObjectStateGroupCreateStructValues
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
        $objectStateGroupCreate->names = [
            'eng-US' => 'Publishing',
            'ger-DE' => 'Sindelfingen',
        ];
        $objectStateGroupCreate->descriptions = [
            'eng-US' => 'Put something online',
            'ger-DE' => 'Put something ton Sindelfingen.',
        ];

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
     * testCreateObjectStateGroupStructValues.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $createdObjectStateGroup
     *
     *
     * @depends testCreateObjectStateGroup
     */
    public function testCreateObjectStateGroupStructValues(ObjectStateGroup $createdObjectStateGroup)
    {
        $this->assertPropertiesCorrect(
            [
                'identifier' => 'publishing',
                'mainLanguageCode' => 'eng-US',
                'languageCodes' => ['eng-US', 'ger-DE'],
                'names' => [
                    'eng-US' => 'Publishing',
                    'ger-DE' => 'Sindelfingen',
                ],
                'descriptions' => [
                    'eng-US' => 'Put something online',
                    'ger-DE' => 'Put something ton Sindelfingen.',
                ],
            ],
            $createdObjectStateGroup
        );
        $this->assertNotNull($createdObjectStateGroup->id);
    }

    /**
     * Test for the createObjectStateGroup() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     * @depends testCreateObjectStateGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateObjectStateGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            // 'ez_lock' is already existing identifier
            'ez_lock'
        );
        $objectStateGroupCreate->defaultLanguageCode = 'eng-US';
        $objectStateGroupCreate->names = [
            'eng-US' => 'Publishing',
            'eng-GB' => 'Sindelfingen',
        ];
        $objectStateGroupCreate->descriptions = [
            'eng-US' => 'Put something online',
            'eng-GB' => 'Put something ton Sindelfingen.',
        ];

        // This call will fail because group with 'ez_lock' identifier already exists
        $objectStateService->createObjectStateGroup(
            $objectStateGroupCreate
        );
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup
     */
    public function testLoadObjectStateGroup()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId('objectstategroup', 2);
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            ObjectStateGroup::class,
            $loadedObjectStateGroup
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 2,
                'identifier' => 'ez_lock',
                'mainLanguageCode' => 'eng-US',
                'languageCodes' => ['eng-US'],
                'names' => ['eng-US' => 'Lock'],
                'descriptions' => ['eng-US' => ''],
            ],
            $loadedObjectStateGroup
        );
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends testLoadObjectStateGroup
     */
    public function testLoadObjectStateGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentObjectStateGroupId = $this->generateId('objectstategroup', self::DB_INT_MAX);
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
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups()
     * @depends testLoadObjectStateGroup
     */
    public function testLoadObjectStateGroups()
    {
        $repository = $this->getRepository();

        $expectedGroupIdentifiers = $this->getGroupIdentifierMap($this->createObjectStateGroups());
        $expectedGroupIdentifiers['ez_lock'] = true;

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups();
        /* END: Use Case */

        $this->assertInternalType('array', $loadedObjectStateGroups);

        $this->assertObjectsLoadedByIdentifiers(
            $expectedGroupIdentifiers,
            $loadedObjectStateGroups,
            'ObjectStateGroup'
        );
    }

    /**
     * Creates a set of object state groups and returns an array of all
     * existing group identifiers after creation.
     *
     * @return bool[]
     */
    protected function createObjectStateGroups()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $identifiersToCreate = [
            'first',
            'second',
            'third',
        ];

        $createdStateGroups = [];

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct('dummy');

        $groupCreateStruct->defaultLanguageCode = 'eng-US';
        $groupCreateStruct->names = [
            'eng-US' => 'Foo',
            'ger-DE' => 'GerFoo',
        ];
        $groupCreateStruct->descriptions = [
            'eng-US' => 'Foo Bar',
            'ger-DE' => 'GerBar',
        ];

        foreach ($identifiersToCreate as $identifier) {
            $groupCreateStruct->identifier = $identifier;
            $createdStateGroups[] = $objectStateService->createObjectStateGroup($groupCreateStruct);
        }

        return $createdStateGroups;
    }

    /**
     * Assert object identifiers.
     *
     * @param array $expectedIdentifiers
     * @param array $loadedObjects
     * @param string $class
     */
    protected function assertObjectsLoadedByIdentifiers(array $expectedIdentifiers, array $loadedObjects, $class)
    {
        foreach ($loadedObjects as $loadedObject) {
            if (!isset($expectedIdentifiers[$loadedObject->identifier])) {
                $this->fail(
                    sprintf(
                        'Loaded not expected %s with identifier "%s"',
                        $class,
                        $loadedObject->identifier
                    )
                );
            }
            unset($expectedIdentifiers[$loadedObject->identifier]);
        }

        if (!empty($expectedIdentifiers)) {
            $this->fail(
                sprintf(
                    'Expected %ss with identifiers "%s" not loaded.',
                    $class,
                    implode('", "', $expectedIdentifiers)
                )
            );
        }
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset)
     * @depends testLoadObjectStateGroups
     */
    public function testLoadObjectStateGroupsWithOffset()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $this->createObjectStateGroups();

        $allObjectStateGroups = $objectStateService->loadObjectStateGroups();

        $existingGroupIdentifiers = $this->getGroupIdentifierMap($allObjectStateGroups);

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups(2);
        /* END: Use Case */

        $this->assertInternalType('array', $loadedObjectStateGroups);

        $this->assertObjectsLoadedByIdentifiers(
            array_slice($existingGroupIdentifiers, 2),
            $loadedObjectStateGroups,
            'ObjectStateGroup'
        );
    }

    /**
     * Returns a map of the given object state groups.
     *
     * @param array $groups
     * @return array
     */
    protected function getGroupIdentifierMap(array $groups)
    {
        $existingGroupIdentifiers = array_map(
            function ($group) {
                return $group->identifier;
            },
            $groups
        );

        return array_fill_keys($existingGroupIdentifiers, true);
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset, $limit)
     * @depends testLoadObjectStateGroupsWithOffset
     */
    public function testLoadObjectStateGroupsWithOffsetAndLimit()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $allObjectStateGroups = $objectStateService->loadObjectStateGroups();

        $existingGroupIdentifiers = $this->getGroupIdentifierMap($allObjectStateGroups);

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups(1, 2);
        /* END: Use Case */

        $this->assertInternalType('array', $loadedObjectStateGroups);

        $this->assertObjectsLoadedByIdentifiers(
            array_slice($existingGroupIdentifiers, 1, 2),
            $loadedObjectStateGroups,
            'ObjectStateGroup'
        );
    }

    /**
     * Test for the loadObjectStates() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStates()
     * @depends testLoadObjectStateGroup
     */
    public function testLoadObjectStates()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId('objectstategroup', 2);
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        // Loads all object states in $objectStateGroup
        $loadedObjectStates = $objectStateService->loadObjectStates($objectStateGroup);
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedObjectStates
        );
        $this->assertObjectsLoadedByIdentifiers(
            ['not_locked' => true, 'locked' => true],
            $loadedObjectStates,
            'ObjectState'
        );
    }

    /**
     * Test for the updateObjectStateGroup() method.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testLoadObjectStateGroup
     */
    public function testUpdateObjectStateGroup()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId('objectstategroup', 2);
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        // pre populate any kind of cache for all
        $objectStateService->loadObjectStateGroups();

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->identifier = 'sindelfingen';
        $groupUpdateStruct->defaultLanguageCode = 'ger-DE';
        $groupUpdateStruct->names = [
            'ger-DE' => 'Sindelfingen',
        ];
        $groupUpdateStruct->descriptions = [
            'ger-DE' => 'Sindelfingen ist nicht nur eine Stadt',
        ];

        // Updates the $loadObjectStateGroup with the data from
        // $groupUpdateStruct and returns the updated group
        $updatedObjectStateGroup = $objectStateService->updateObjectStateGroup(
            $loadedObjectStateGroup,
            $groupUpdateStruct
        );

        $allObjectGroups = $objectStateService->loadObjectStateGroups();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $updatedObjectStateGroup
        );

        return [
            $loadedObjectStateGroup,
            $groupUpdateStruct,
            $updatedObjectStateGroup,
            $allObjectGroups,
        ];
    }

    /**
     * Test service method for partially updating object state group.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testLoadObjectStateGroup
     */
    public function testUpdateObjectStateGroupChosenFieldsOnly()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->defaultLanguageCode = 'eng-GB';
        $groupUpdateStruct->names = ['eng-GB' => 'Test'];

        $group = $objectStateService->loadObjectStateGroup(2);

        $updatedGroup = $objectStateService->updateObjectStateGroup($group, $groupUpdateStruct);

        $this->assertInstanceOf(
            ObjectStateGroup::class,
            $updatedGroup
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 2,
                'identifier' => 'ez_lock',
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB'],
                'names' => ['eng-GB' => 'Test'],
                // descriptions array should have an empty value for eng-GB
                // without the original descriptions
                // since the descriptions were not in the update struct and we're changing default language
                'descriptions' => ['eng-GB' => ''],
            ],
            $updatedGroup
        );
    }

    /**
     * Test for the updateObjectStateGroup() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup()
     * @depends testUpdateObjectStateGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateObjectStateGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateService = $repository->getObjectStateService();

        // Create object state group which we will later update
        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            'publishing'
        );
        $objectStateGroupCreate->defaultLanguageCode = 'eng-US';
        $objectStateGroupCreate->names = [
            'eng-US' => 'Publishing',
            'eng-GB' => 'Sindelfingen',
        ];
        $objectStateGroupCreate->descriptions = [
            'eng-US' => 'Put something online',
            'eng-GB' => 'Put something ton Sindelfingen.',
        ];

        $createdObjectStateGroup = $objectStateService->createObjectStateGroup(
            $objectStateGroupCreate
        );

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        // 'ez_lock' is the identifier of already existing group
        $groupUpdateStruct->identifier = 'ez_lock';
        $groupUpdateStruct->defaultLanguageCode = 'ger-DE';
        $groupUpdateStruct->names = [
            'ger-DE' => 'Sindelfingen',
        ];
        $groupUpdateStruct->descriptions = [
            'ger-DE' => 'Sindelfingen ist nicht nur eine Stadt',
        ];

        // This call will fail since state group with 'ez_lock' identifier already exists
        $objectStateService->updateObjectStateGroup(
            $createdObjectStateGroup,
            $groupUpdateStruct
        );
    }

    /**
     * testUpdateObjectStateGroupStructValues.
     *
     * @param array $testData
     *
     *
     * @depends testUpdateObjectStateGroup
     */
    public function testUpdateObjectStateGroupStructValues(array $testData)
    {
        list(
            $loadedObjectStateGroup,
            $groupUpdateStruct,
            $updatedObjectStateGroup,
            $allObjectGroups
        ) = $testData;

        $this->assertStructPropertiesCorrect(
            $groupUpdateStruct,
            $updatedObjectStateGroup
        );

        $this->assertContains($updatedObjectStateGroup, $allObjectGroups, '', false, false);
        $this->assertNotContains($loadedObjectStateGroup, $allObjectGroups, '', false, false);
    }

    /**
     * Test for the createObjectState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     * @depends testLoadObjectStateGroup
     * @depends testNewObjectStateCreateStruct
     */
    public function testCreateObjectState()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId('objectstategroup', 2);
        /* BEGIN: Use Case */
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
        $objectStateCreateStruct->names = [
            'eng-US' => 'Locked and Unlocked',
            'ger-DE' => 'geschlossen und ungeschlossen',
        ];
        $objectStateCreateStruct->descriptions = [
            'eng-US' => 'A state between locked and unlocked.',
            'ger-DE' => 'ein Zustand zwischen geschlossen und ungeschlossen.',
        ];

        // Creates a new object state in the $loadObjectStateGroup with the
        // data from $objectStateCreateStruct
        $createdObjectState = $objectStateService->createObjectState(
            $loadedObjectStateGroup,
            $objectStateCreateStruct
        );
        /* END: Use Case */

        $this->assertInstanceOf(ObjectState::class, $createdObjectState);
        // Object sequences are renumbered
        $objectStateCreateStruct->priority = 2;

        return [
            $loadedObjectStateGroup,
            $objectStateCreateStruct,
            $createdObjectState,
        ];
    }

    /**
     * Test service method for creating object state in empty group.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::createObjectState
     */
    public function testCreateObjectStateInEmptyGroup()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct('test');
        $groupCreateStruct->defaultLanguageCode = 'eng-GB';
        $groupCreateStruct->names = ['eng-GB' => 'Test'];
        $groupCreateStruct->descriptions = ['eng-GB' => 'Test description'];

        $createdGroup = $objectStateService->createObjectStateGroup($groupCreateStruct);

        $stateCreateStruct = $objectStateService->newObjectStateCreateStruct('test');
        $stateCreateStruct->priority = 2;
        $stateCreateStruct->defaultLanguageCode = 'eng-GB';
        $stateCreateStruct->names = ['eng-GB' => 'Test'];
        $stateCreateStruct->descriptions = ['eng-GB' => 'Test description'];

        $createdState = $objectStateService->createObjectState(
            $createdGroup,
            $stateCreateStruct
        );

        $this->assertInstanceOf(
            ObjectState::class,
            $createdState
        );

        $this->assertNotNull($createdState->id);
        $this->assertPropertiesCorrect(
            [
                'identifier' => 'test',
                'priority' => 0,
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB'],
                'names' => ['eng-GB' => 'Test'],
                'descriptions' => ['eng-GB' => 'Test description'],
            ],
            $createdState
        );

        $objectStateGroup = $createdState->getObjectStateGroup();
        $this->assertInstanceOf(
            ObjectStateGroup::class,
            $objectStateGroup
        );

        $this->assertEquals($createdGroup->id, $objectStateGroup->id);
        $this->assertGreaterThan(0, $objectStateService->getContentCount($createdState));
    }

    /**
     * Test for the createObjectState() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     * @depends testLoadObjectStateGroup
     * @depends testCreateObjectState
     */
    public function testCreateObjectStateThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId('objectstategroup', 2);
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            // 'not_locked' is the identifier of already existing state
            'not_locked'
        );
        $objectStateCreateStruct->priority = 23;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = [
            'eng-US' => 'Locked and Unlocked',
        ];
        $objectStateCreateStruct->descriptions = [
            'eng-US' => 'A state between locked and unlocked.',
        ];

        // This call will fail because object state with
        // 'not_locked' identifier already exists
        $objectStateService->createObjectState(
            $loadedObjectStateGroup,
            $objectStateCreateStruct
        );
    }

    /**
     * testCreateObjectStateStructValues.
     *
     * @param array $testData
     *
     *
     * @depends testCreateObjectState
     */
    public function testCreateObjectStateStructValues(array $testData)
    {
        list(
            $loadedObjectStateGroup,
            $objectStateCreateStruct,
            $createdObjectState
        ) = $testData;

        $this->assertStructPropertiesCorrect(
            $objectStateCreateStruct,
            $createdObjectState
        );

        $this->assertNotNull($createdObjectState->id);

        $this->assertEquals(
            $loadedObjectStateGroup,
            $createdObjectState->getObjectStateGroup()
        );
    }

    /**
     * Test for the loadObjectState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     * @depends testLoadObjectStateGroup
     */
    public function testLoadObjectState()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId('objectstate', 2);
        /* BEGIN: Use Case */
        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $loadedObjectState
        );

        return $loadedObjectState;
    }

    /**
     * testLoadObjectStateStructValues.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $loadedObjectState
     *
     *
     * @depends testLoadObjectState
     */
    public function testLoadObjectStateStructValues(ObjectState $loadedObjectState)
    {
        $this->assertPropertiesCorrect(
            [
                'id' => 2,
                'identifier' => 'locked',
                'priority' => 1,
                'mainLanguageCode' => 'eng-US',
                'languageCodes' => [0 => 'eng-US'],
                'names' => ['eng-US' => 'Locked'],
                'descriptions' => ['eng-US' => ''],
            ],
            $loadedObjectState
        );

        $this->assertEquals(
            $this->getRepository()->getObjectStateService()->loadObjectStateGroup(2),
            $loadedObjectState->getObjectStateGroup()
        );
    }

    /**
     * Test for the loadObjectState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends testLoadObjectState
     */
    public function testLoadObjectStateThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingObjectStateId = $this->generateId('objectstate', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        // $nonExistingObjectStateId contains the ID of a non existing state
        $objectStateService = $repository->getObjectStateService();

        // Throws not found exception
        $loadedObjectState = $objectStateService->loadObjectState(
            $nonExistingObjectStateId
        );
        /* END: Use Case */
    }

    /**
     * Data provider for PrioritizedLanguageList tests.
     *
     * @return array
     */
    public function getPrioritizedLanguagesList()
    {
        return [
            [[], null],
            [['eng-GB'], null],
            [['eng-US'], 'eng-US'],
            [['ger-DE'], 'ger-DE'],
            [['eng-US', 'ger-DE'], 'eng-US'],
            [['ger-DE', 'eng-US'], 'ger-DE'],
            [['eng-GB', 'ger-DE', 'eng-US'], 'ger-DE'],
        ];
    }

    /**
     * Test that multi-language logic for loadObjectStateGroups respects prioritized language list.
     *
     * @dataProvider getPrioritizedLanguagesList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode
     */
    public function testLoadObjectStateGroupsWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        // cleanup before the actual test
        $this->deleteExistingObjectStateGroups();

        $repository = $this->getRepository(false);
        $objectStateService = $repository->getObjectStateService();

        $this->createObjectStateGroups();

        $objectStateGroups = $objectStateService->loadObjectStateGroups(
            0,
            -1,
            $prioritizedLanguages
        );

        foreach ($objectStateGroups as $objectStateGroup) {
            $languageCode = $expectedLanguageCode === null ? $objectStateGroup->defaultLanguageCode : $expectedLanguageCode;

            self::assertEquals(
                $objectStateGroup->getName($languageCode),
                $objectStateGroup->getName()
            );

            self::assertEquals(
                $objectStateGroup->getDescription($languageCode),
                $objectStateGroup->getDescription()
            );
        }
    }

    /**
     * Test that multi-language logic for loadObjectStateGroup respects prioritized language list.
     *
     * @dataProvider getPrioritizedLanguagesList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode
     */
    public function testLoadObjectStateGroupWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroup = $this->testCreateObjectStateGroup();
        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroup->id,
            $prioritizedLanguages
        );

        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $loadedObjectStateGroup->defaultLanguageCode;
        }

        self::assertEquals(
            $loadedObjectStateGroup->getName($expectedLanguageCode),
            $loadedObjectStateGroup->getName()
        );

        self::assertEquals(
            $loadedObjectStateGroup->getDescription($expectedLanguageCode),
            $loadedObjectStateGroup->getDescription()
        );
    }

    /**
     * Test that multi-language logic for loadObjectState respects prioritized language list.
     *
     * @dataProvider getPrioritizedLanguagesList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode
     */
    public function testLoadObjectStateWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $objectStateData = $this->testCreateObjectState();
        /** @see \eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testCreateObjectState */
        $objectState = $objectStateData[2];
        /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState */
        $loadedObjectState = $objectStateService->loadObjectState($objectState->id, $prioritizedLanguages);

        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $objectState->defaultLanguageCode;
        }

        self::assertEquals(
            $loadedObjectState->getName($expectedLanguageCode),
            $loadedObjectState->getName()
        );

        self::assertEquals(
            $loadedObjectState->getDescription($expectedLanguageCode),
            $loadedObjectState->getDescription()
        );
    }

    /**
     * Test that multi-language logic for loadObjectStates respects prioritized language list.
     *
     * @dataProvider getPrioritizedLanguagesList
     * @param string[] $languageCodes
     * @param string|null $expectedLanguageCode
     */
    public function testLoadObjectStatesWithPrioritizedLanguagesList($languageCodes, $expectedLanguageCode)
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroup = $this->testCreateObjectStateGroup();
        $this->createObjectState(
            $objectStateGroup,
            'state_1',
            [
                'eng-US' => 'One',
                'ger-DE' => 'ein',
            ],
            [
                'eng-US' => 'State one',
                'ger-DE' => 'ein Zustand',
            ]
        );
        $this->createObjectState(
            $objectStateGroup,
            'state_2',
            [
                'eng-US' => 'Two',
                'ger-DE' => 'zwei',
            ],
            [
                'eng-US' => 'State two',
                'ger-DE' => 'zwei Zustand',
            ]
        );

        // Loads all object states in $objectStateGroup
        $loadedObjectStates = $objectStateService->loadObjectStates($objectStateGroup, $languageCodes);

        foreach ($loadedObjectStates as $objectState) {
            self::assertEquals(
                $objectState->getName($expectedLanguageCode),
                $objectState->getName()
            );

            self::assertEquals(
                $objectState->getDescription($expectedLanguageCode),
                $objectState->getDescription()
            );
        }
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectState
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testLoadObjectState
     */
    public function testUpdateObjectState()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId('objectstate', 2);
        /* BEGIN: Use Case */
        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        // pre load any possile cache loading all
        $objectStateService->loadObjectStates($loadedObjectState->getObjectStateGroup());

        $updateStateStruct = $objectStateService->newObjectStateUpdateStruct();
        $updateStateStruct->identifier = 'somehow_locked';
        $updateStateStruct->defaultLanguageCode = 'ger-DE';
        $updateStateStruct->names = [
            'eng-US' => 'Somehow locked',
            'ger-DE' => 'Irgendwie gelockt',
        ];
        $updateStateStruct->descriptions = [
            'eng-US' => 'The object is somehow locked',
            'ger-DE' => 'Sindelfingen',
        ];

        $updatedObjectState = $objectStateService->updateObjectState(
            $loadedObjectState,
            $updateStateStruct
        );

        $allObjectStates = $objectStateService->loadObjectStates($loadedObjectState->getObjectStateGroup());
        /* END: Use Case */

        $this->assertInstanceOf(
            ObjectState::class,
            $updatedObjectState
        );

        return [
            $loadedObjectState,
            $updateStateStruct,
            $updatedObjectState,
            $allObjectStates,
        ];
    }

    /**
     * Test service method for partially updating object state.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectState
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testLoadObjectState
     */
    public function testUpdateObjectStateChosenFieldsOnly()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $stateUpdateStruct = $objectStateService->newObjectStateUpdateStruct();
        $stateUpdateStruct->identifier = 'test';
        $stateUpdateStruct->names = ['eng-US' => 'Test'];

        $state = $objectStateService->loadObjectState(1);

        $updatedState = $objectStateService->updateObjectState($state, $stateUpdateStruct);

        $this->assertInstanceOf(
            ObjectState::class,
            $updatedState
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 1,
                'identifier' => 'test',
                'priority' => 0,
                'mainLanguageCode' => 'eng-US',
                'languageCodes' => ['eng-US'],
                'names' => ['eng-US' => 'Test'],
                // Original value of empty description for eng-US should be kept
                'descriptions' => ['eng-US' => ''],
            ],
            $updatedState
        );

        $this->assertInstanceOf(
            ObjectStateGroup::class,
            $updatedState->getObjectStateGroup()
        );

        $this->assertEquals($state->getObjectStateGroup()->id, $updatedState->getObjectStateGroup()->id);
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     * @depends testUpdateObjectState
     */
    public function testUpdateObjectStateThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId('objectstate', 2);
        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        $updateStateStruct = $objectStateService->newObjectStateUpdateStruct();
        // 'not_locked' is the identifier of already existing state
        $updateStateStruct->identifier = 'not_locked';
        $updateStateStruct->defaultLanguageCode = 'ger-DE';
        $updateStateStruct->names = [
            'eng-US' => 'Somehow locked',
            'ger-DE' => 'Irgendwie gelockt',
        ];
        $updateStateStruct->descriptions = [
            'eng-US' => 'The object is somehow locked',
            'ger-DE' => 'Sindelfingen',
        ];

        // This call will fail because state with
        // 'not_locked' identifier already exists
        $objectStateService->updateObjectState(
            $loadedObjectState,
            $updateStateStruct
        );
    }

    /**
     * testUpdateObjectStateStructValues.
     *
     * @param array $testData
     *
     *
     * @depends testUpdateObjectState
     */
    public function testUpdateObjectStateStructValues(array $testData)
    {
        list(
            $loadedObjectState,
            $updateStateStruct,
            $updatedObjectState,
            $allObjectStates
        ) = $testData;

        $this->assertPropertiesCorrect(
            [
                'id' => $loadedObjectState->id,
                'identifier' => $updateStateStruct->identifier,
                'priority' => $loadedObjectState->priority,
                'mainLanguageCode' => $updateStateStruct->defaultLanguageCode,
                'languageCodes' => ['eng-US', 'ger-DE'],
                'names' => $updateStateStruct->names,
                'descriptions' => $updateStateStruct->descriptions,
            ],
            $updatedObjectState
        );

        $this->assertEquals(
            $loadedObjectState->getObjectStateGroup(),
            $updatedObjectState->getObjectStateGroup()
        );

        $this->assertContains($updatedObjectState, $allObjectStates, '', false, false);
        $this->assertNotContains($loadedObjectState, $allObjectStates, '', false, false);
    }

    /**
     * Test for the setPriorityOfObjectState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::setPriorityOfObjectState()
     * @depends testLoadObjectState
     */
    public function testSetPriorityOfObjectState()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId('objectstate', 1);
        /* BEGIN: Use Case */
        // $objectStateId contains the ID of the "not_locked" state
        $objectStateService = $repository->getObjectStateService();

        $initiallyLoadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        // Sets the given priority on $initiallyLoadedObjectState
        $objectStateService->setPriorityOfObjectState(
            $initiallyLoadedObjectState,
            23
        );
        // $loadObjectState now has the priority 1, since object state
        // priorities are always made sequential
        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $loadedObjectState
        );
        $this->assertEquals(1, $loadedObjectState->priority);
    }

    /**
     * Test for the getContentState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::getContentState()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends testLoadObjectState
     */
    public function testGetContentState()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        $ezLockObjectStateGroupId = $this->generateId('objectstategroup', 2);
        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        $contentService = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $ezLockObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $ezLockObjectStateGroupId
        );

        // Loads the state of $contentInfo in the "ez_lock" object state group
        $ezLockObjectState = $objectStateService->getContentState(
            $contentInfo,
            $ezLockObjectStateGroup
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $ezLockObjectState
        );
        $this->assertEquals('not_locked', $ezLockObjectState->identifier);
    }

    /**
     * testGetInitialObjectState.
     *
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends testLoadObjectState
     */
    public function testGetInitialObjectState()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        // Create object state group with custom state
        $createdStateGroups = $this->createObjectStateGroups();

        $customObjectStateGroupId = $createdStateGroups[1]->id;
        $anonymousUserId = $this->generateId('user', 10);

        $customGroup = $objectStateService->loadObjectStateGroup(
            $customObjectStateGroupId
        );

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            'sindelfingen'
        );
        $objectStateCreateStruct->priority = 1;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = ['eng-US' => 'Sindelfingen'];

        $createdState = $objectStateService->createObjectState(
            $customGroup,
            $objectStateCreateStruct
        );

        // Store state ID to be used
        $customObjectStateId = $createdState->id;

        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        // $customObjectStateGroupId is the ID of a state group, from which no
        // state has been assigned to $anonymousUserId, yet
        $contentService = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $customObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $customObjectStateGroupId
        );

        // Loads the initial state of the custom state group
        $initialObjectState = $objectStateService->getContentState(
            $contentInfo,
            $customObjectStateGroup
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $initialObjectState
        );
        $this->assertEquals('sindelfingen', $initialObjectState->identifier);
        $this->assertEquals(['eng-US' => 'Sindelfingen'], $initialObjectState->names);
        $this->assertEquals('eng-US', $initialObjectState->defaultLanguageCode);
    }

    /**
     * Test for the setContentState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::setContentState()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends testLoadObjectState
     */
    public function testSetContentState()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        $ezLockObjectStateGroupId = $this->generateId('objectstategroup', 2);
        $lockedObjectStateId = $this->generateId('objectstate', 2);
        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        // $ezLockObjectStateGroupId contains the ID of the "ez_lock" object
        // state group
        // $lockedObjectStateId is the ID of the state "locked"
        $contentService = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $ezLockObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $ezLockObjectStateGroupId
        );
        $lockedObjectState = $objectStateService->loadObjectState($lockedObjectStateId);

        // Sets the state of $contentInfo from "not_locked" to "locked"
        $objectStateService->setContentState(
            $contentInfo,
            $ezLockObjectStateGroup,
            $lockedObjectState
        );
        /* END: Use Case */

        $ezLockObjectState = $objectStateService->getContentState(
            $contentInfo,
            $ezLockObjectStateGroup
        );

        $this->assertEquals('locked', $ezLockObjectState->identifier);
    }

    /**
     * Test for the setContentState() method.
     *
     * @covers \eZ\Publish\API\Repository\ObjectStateService::setContentState
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ObjectStateServiceTest::testSetContentState
     */
    public function testSetContentStateThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $createdStateGroups = $this->createObjectStateGroups();

        $anonymousUserId = $this->generateId('user', 10);
        $differentObjectStateGroupId = $createdStateGroups[1]->id;
        $lockedObjectStateId = $this->generateId('objectstate', 2);

        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        // $differentObjectStateGroupId contains the ID of an object state
        // group which does not contain $lockedObjectStateId
        // $lockedObjectStateId is the ID of the state "locked"
        $contentService = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $differentObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $differentObjectStateGroupId
        );
        $lockedObjectState = $objectStateService->loadObjectState($lockedObjectStateId);

        // Throws an invalid argument exception since $lockedObjectState does
        // not belong to $differentObjectStateGroup
        $objectStateService->setContentState(
            $contentInfo,
            $differentObjectStateGroup,
            $lockedObjectState
        );
        /* END: Use Case */
    }

    /**
     * Test for the getContentCount() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::getContentCount()
     * @depends testLoadObjectState
     */
    public function testGetContentCount()
    {
        $repository = $this->getRepository();

        $notLockedObjectStateId = $this->generateId('objectstate', 1);
        /* BEGIN: Use Case */
        // $notLockedObjectStateId is the ID of the state "not_locked"
        $objectStateService = $repository->getObjectStateService();

        $notLockedObjectState = $objectStateService->loadObjectState($notLockedObjectStateId);

        $objectCount = $objectStateService->getContentCount($notLockedObjectState);
        /* END: Use Case */

        $this->assertEquals(18, $objectCount);
    }

    /**
     * Test for the deleteObjectState() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectState()
     * @depends testLoadObjectState
     */
    public function testDeleteObjectState()
    {
        $repository = $this->getRepository();

        $notLockedObjectStateId = $this->generateId('objectstate', 1);
        $lockedObjectStateId = $this->generateId('objectstate', 2);
        /* BEGIN: Use Case */
        // $notLockedObjectStateId is the ID of the state "not_locked"
        $objectStateService = $repository->getObjectStateService();

        $notLockedObjectState = $objectStateService->loadObjectState($notLockedObjectStateId);

        // Deletes the object state and sets all objects, which where in that
        // state, to the first state of the same object state group
        $objectStateService->deleteObjectState($notLockedObjectState);
        /* END: Use Case */

        $lockedObjectState = $objectStateService->loadObjectState($lockedObjectStateId);

        // All objects transferred
        $this->assertEquals(
            18,
            $objectStateService->getContentCount($lockedObjectState)
        );
    }

    /**
     * Test for the deleteObjectStateGroup() method.
     *
     *
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup()
     * @depends testLoadObjectStateGroup
     */
    public function testDeleteObjectStateGroup()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId('objectstategroup', 2);
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $objectStateService->deleteObjectStateGroup($loadedObjectStateGroup);
        /* END: Use Case */

        try {
            $objectStateService->loadObjectStateGroup($objectStateGroupId);
            $this->fail(
                sprintf(
                    'ObjectStateGroup with ID "%s" not deleted.',
                    $objectStateGroupId
                )
            );
        } catch (NotFoundException $e) {
        }
    }

    /**
     * Delete existing (e.g. initial) object state groups.
     */
    private function deleteExistingObjectStateGroups()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroups = $objectStateService->loadObjectStateGroups();

        foreach ($objectStateGroups as $objectStateGroup) {
            $objectStateService->deleteObjectStateGroup($objectStateGroup);
        }
    }

    /**
     * Create Object State within the given Object State Group.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param string $identifier
     * @param array $names multi-language names
     * @param array $descriptions multi-language descriptions
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    private function createObjectState(
        ObjectStateGroup $objectStateGroup,
        $identifier,
        array $names,
        array $descriptions
    ) {
        $objectStateService = $this->getRepository(false)->getObjectStateService();
        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            $identifier
        );
        $objectStateCreateStruct->priority = 23;
        $objectStateCreateStruct->defaultLanguageCode = array_keys($names)[0];
        $objectStateCreateStruct->names = $names;
        $objectStateCreateStruct->descriptions = $descriptions;

        // Create a new object state in the $objectStateGroup with the
        // data from $objectStateCreateStruct
        return $objectStateService->createObjectState(
            $objectStateGroup,
            $objectStateCreateStruct
        );
    }
}
