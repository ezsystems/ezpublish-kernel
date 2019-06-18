<?php

/**
 * File contains: Abstract Base service test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use PHPUnit_Framework_TestCase;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Base test case for tests on services
 * Initializes repository.
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * Setup test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->repository = static::getRepository();
        $this->repository->setCurrentUser($this->getStubbedUser(14));
    }

    /**
     * Returns User stub with $id as User/Content id.
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function getStubbedUser($id)
    {
        return new User(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(['id' => $id]),
                            ]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\User\User
     */
    protected function createUserVersion1()
    {
        $repository = $this->repository;

        /* BEGIN: Inline */
        // ID of the "Editors" user group in an eZ Publish demo installation
        $editorsGroupId = 13;

        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;

        // Set some fields required by the user ContentType
        $userCreate->setField('first_name', 'Example');
        $userCreate->setField('last_name', 'User');

        // Load parent group for the user
        $group = $userService->loadUserGroup($editorsGroupId);

        // Create a new user instance.
        $user = $userService->createUser($userCreate, [$group]);
        /* END: Inline */

        return $user;
    }

    /**
     * Tear down test (properties).
     */
    protected function tearDown()
    {
        unset($this->repository);
        parent::tearDown();
    }

    /**
     * Generate \eZ\Publish\API\Repository\Repository.
     *
     * Makes it possible to inject different Io / Persistence handlers
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    abstract protected function getRepository();

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $skipProperties
     */
    protected function assertPropertiesCorrect(array $expectedValues, ValueObject $actualObject, array $skipProperties = [])
    {
        foreach ($expectedValues as $propertyName => $propertyValue) {
            if (in_array($propertyName, $skipProperties)) {
                continue;
            }

            $this->assertProperty($propertyName, $propertyValue, $actualObject->$propertyName);
        }
    }

    protected function assertSameClassPropertiesCorrect(
        array $propertiesNames,
        ValueObject $expectedValues,
        ValueObject $actualObject,
        array $skipProperties = [],
        $equal = true
    ) {
        foreach ($propertiesNames as $propertyName) {
            if (in_array($propertyName, $skipProperties)) {
                continue;
            }

            $this->assertProperty($propertyName, $expectedValues->$propertyName, $actualObject->$propertyName, $equal);
        }
    }

    /**
     * Asserts all properties from $expectedValues are correctly set in
     * $actualObject.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $skipProperties
     */
    protected function assertStructPropertiesCorrect(ValueObject $expectedValues, ValueObject $actualObject, array $skipProperties = [])
    {
        foreach ($expectedValues as $propertyName => $propertyValue) {
            if (in_array($propertyName, $skipProperties)) {
                continue;
            }

            $this->assertProperty($propertyName, $propertyValue, $actualObject->$propertyName);
        }
    }

    private function assertProperty($propertyName, $expectedValue, $actualValue, $equal = true)
    {
        if ($expectedValue instanceof \ArrayObject) {
            $expectedValue = $expectedValue->getArrayCopy();
        }
        if ($actualValue instanceof \ArrayObject) {
            $actualValue = $actualValue->getArrayCopy();
        }

        // For PHP 7.1 make sure we just compare the timestamp and not the offset value
        if ($expectedValue instanceof \DateTimeInterface) {
            $expectedValue = $expectedValue->getTimestamp();
        }
        if ($actualValue instanceof \DateTimeInterface) {
            $actualValue = $actualValue->getTimestamp();
        }

        if ($equal) {
            $this->assertEquals(
                $expectedValue,
                $actualValue,
                sprintf('Object property "%s" incorrect.', $propertyName)
            );
        } else {
            $this->assertNotEquals(
                $expectedValue,
                $actualValue,
                sprintf('Object property "%s" incorrect.', $propertyName)
            );
        }
    }

    protected function getDateTime($timestamp)
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }
}
