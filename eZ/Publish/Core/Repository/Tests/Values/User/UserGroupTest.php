<?php

/**
 * File containing the UserGroupTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Values\User;

use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use PHPUnit\Framework\TestCase;

/**
 * Test internal integrity of @see \eZ\Publish\Core\Repository\Values\User\UserGroup.
 */
class UserGroupTest extends TestCase
{
    use ValueObjectTestTrait;

    /**
     * @covers \eZ\Publish\Core\Repository\Values\User\UserGroup::__construct
     */
    public function testNewClass()
    {
        $group = new UserGroup();
        self::assertEquals(null, $group->parentId);

        $this->assertPropertiesCorrect(
            [
                'parentId' => null,
            ],
            $group
        );
    }

    /**
     * Test getName method.
     *
     * @covers \eZ\Publish\Core\Repository\Values\User\UserGroup::getName
     */
    public function testGetName()
    {
        $name = 'Translated name';
        $contentMock = $this->createMock(Content::class);
        $versionInfoMock = $this->createMock(VersionInfo::class);

        $contentMock->expects($this->once())
            ->method('getVersionInfo')
            ->willReturn($versionInfoMock);

        $versionInfoMock->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $object = new UserGroup(['content' => $contentMock]);

        $this->assertEquals($name, $object->getName());
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testMissingProperty()
    {
        $userGroup = new UserGroup();
        $value = $userGroup->notDefined;
        self::fail('Succeeded getting non existing property');
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\User\UserGroup::getProperties
     */
    public function testObjectProperties()
    {
        $object = new UserGroup();
        $properties = $object->attributes();
        self::assertNotContains('internalFields', $properties, 'Internal property found ');
        self::assertContains('id', $properties, 'Property not found ');
        self::assertContains('fields', $properties, 'Property not found ');
        self::assertContains('versionInfo', $properties, 'Property not found ');
        self::assertContains('contentInfo', $properties, 'Property not found ');

        // check for duplicates and double check existence of property
        $propertiesHash = [];
        foreach ($properties as $property) {
            if (isset($propertiesHash[$property])) {
                self::fail("Property '{$property}' exists several times in properties list");
            } elseif (!isset($object->$property)) {
                self::fail("Property '{$property}' does not exist on object, even though it was hinted to be there");
            }
            $propertiesHash[$property] = 1;
        }
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__set
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testReadOnlyProperty()
    {
        $userGroup = new UserGroup();
        $userGroup->parentId = 42;
        self::fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__isset
     */
    public function testIsPropertySet()
    {
        $userGroup = new UserGroup();
        $value = isset($userGroup->notDefined);
        self::assertEquals(false, $value);

        $value = isset($userGroup->parentId);
        self::assertEquals(true, $value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__unset
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testUnsetProperty()
    {
        $userGroup = new UserGroup(['parentId' => 1]);
        unset($userGroup->parentId);
        self::fail('Unsetting read-only property succeeded');
    }
}
