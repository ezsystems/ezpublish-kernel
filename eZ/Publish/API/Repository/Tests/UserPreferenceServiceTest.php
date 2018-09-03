<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceSetStruct;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreference;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList;

/**
 * Test case for the UserPreferenceService.
 *
 * @see \eZ\Publish\API\Repository\UserPreferenceService
 */
class UserPreferenceServiceTest extends BaseTest
{
    /**
     * @covers \eZ\Publish\API\Repository\UserPreferenceService::loadUserPreferences()
     */
    public function testLoadUserPreferences()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userPreferenceService = $repository->getUserPreferenceService();
        $userPreferenceList = $userPreferenceService->loadUserPreferences(0, 25);
        /* END: Use Case */

        $this->assertInstanceOf(UserPreferenceList::class, $userPreferenceList);
        $this->assertInternalType('array', $userPreferenceList->items);
        $this->assertInternalType('int', $userPreferenceList->totalCount);
        $this->assertEquals(5, $userPreferenceList->totalCount);
    }

    /**
     * @covers \eZ\Publish\API\Repository\UserPreferenceService::getUserPreference()
     */
    public function testGetUserPreference()
    {
        $repository = $this->getRepository();

        $userPreferenceName = 'setting_1';

        /* BEGIN: Use Case */
        $userPreferenceService = $repository->getUserPreferenceService();
        // $userPreferenceName is the name of an existing preference
        $userPreference = $userPreferenceService->getUserPreference($userPreferenceName);
        /* END: Use Case */

        $this->assertInstanceOf(UserPreference::class, $userPreference);
        $this->assertEquals($userPreferenceName, $userPreference->name);
    }

    /**
     * @covers \eZ\Publish\API\Repository\UserPreferenceService::setUserPreference()
     * @depends testGetUserPreference
     */
    public function testSetUserPreference()
    {
        $repository = $this->getRepository();

        $userPreferenceName = 'timezone';

        /* BEGIN: Use Case */
        $userPreferenceService = $repository->getUserPreferenceService();

        $setStruct = new UserPreferenceSetStruct([
            'name' => $userPreferenceName,
            'value' => 'America/New_York',
        ]);

        $userPreferenceService->setUserPreference([$setStruct]);
        $userPreference = $userPreferenceService->getUserPreference($userPreferenceName);
        /* END: Use Case */

        $this->assertInstanceOf(UserPreference::class, $userPreference);
        $this->assertEquals($userPreferenceName, $userPreference->name);
    }

    /**
     * @covers \eZ\Publish\API\Repository\UserPreferenceService::setUserPreference()
     * @depends testSetUserPreference
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function testSetUserPreferenceThrowsInvalidArgumentExceptionOnInvalidValue()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userPreferenceService = $repository->getUserPreferenceService();

        $setStruct = new UserPreferenceSetStruct([
            'name' => 'setting',
            'value' => new \stdClass(),
        ]);

        // This call will fail because value is not specified
        $userPreferenceService->setUserPreference([$setStruct]);
        /* END: Use Case */
    }

    /**
     * @covers \eZ\Publish\API\Repository\UserPreferenceService::setUserPreference()
     * @depends testSetUserPreference
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSetUserPreferenceThrowsInvalidArgumentExceptionOnEmptyName()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userPreferenceService = $repository->getUserPreferenceService();

        $setStruct = new UserPreferenceSetStruct([
            'value' => 'value',
        ]);

        // This call will fail because value is not specified
        $userPreferenceService->setUserPreference([$setStruct]);
        /* END: Use Case */
    }

    /**
     * @covers \eZ\Publish\API\Repository\UserPreferenceService::getUserPreferenceCount()
     */
    public function testGetUserPreferenceCount()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userPreferenceService = $repository->getUserPreferenceService();
        $userPreferenceCount = $userPreferenceService->getUserPreferenceCount();
        /* END: Use Case */

        $this->assertEquals(5, $userPreferenceCount);
    }
}
