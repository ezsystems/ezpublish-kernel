<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\Tests\FieldsGroups;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Helper\FieldsGroups\ArrayTranslatorFieldsGroupsList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArrayTranslatorFieldsGroupsListTest extends TestCase
{
    private const FIRST_GROUP_ID = 'slayer';
    private const SECOND_GROUP_ID = 'system_of_a_down';
    private const FIRST_GROUP_NAME = 'Slayer';
    private const SECOND_GROUP_NAME = 'System of a down';

    private const ALL_GROUPS_IDS = [self::FIRST_GROUP_ID, self::SECOND_GROUP_ID];
    private const DEFAULT_GROUP_ID = self::SECOND_GROUP_ID;
    private const DEFAULT_GROUP_NAME = self::SECOND_GROUP_NAME;

    private $translatorMock;

    /**
     * @covers \eZ\Publish\Core\Helper\FieldsGroups\ArrayTranslatorFieldsGroupsList::getGroups
     */
    public function testTranslatesGroups(): void
    {
        $this->applyTranslationsForTranslationsMock();

        $arrayTranslatorFieldsGroupsList = $this->getArrayTranslatorFieldsGroupsList();

        self::assertEquals(
            [
                self::FIRST_GROUP_ID => self::FIRST_GROUP_NAME,
                self::SECOND_GROUP_ID => self::SECOND_GROUP_NAME,
            ],
            $arrayTranslatorFieldsGroupsList->getGroups()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Helper\FieldsGroups\ArrayTranslatorFieldsGroupsList::getDefaultGroup
     */
    public function testReturnsDefault(): void
    {
        $list = $this->getArrayTranslatorFieldsGroupsList([]);

        self::assertEquals(self::DEFAULT_GROUP_ID, $list->getDefaultGroup());
    }

    /**
     * @covers \eZ\Publish\Core\Helper\FieldsGroups\ArrayTranslatorFieldsGroupsList::getFieldGroup
     */
    public function testGetFieldGroupWhenFieldDefinitionHasGroup(): void
    {
        $fieldDefinitionMock = $this->getFieldDefinitionMock(
            [['fieldGroup' => self::FIRST_GROUP_ID]],
        );

        $arrayTranslatorFieldsGroupsList = $this->getArrayTranslatorFieldsGroupsList();

        $this->assertSame(
            $fieldDefinitionMock->fieldGroup,
            $arrayTranslatorFieldsGroupsList->getFieldGroup($fieldDefinitionMock)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Helper\FieldsGroups\ArrayTranslatorFieldsGroupsList::getFieldGroup
     */
    public function testGetFieldGroupWhenFieldDefinitionMissingGroup(): void
    {
        $fieldDefinitionMock = $this->getFieldDefinitionMock();

        $arrayTranslatorFieldsGroupsList = $this->getArrayTranslatorFieldsGroupsList();

        $this->assertSame(
            self::DEFAULT_GROUP_ID,
            $arrayTranslatorFieldsGroupsList->getFieldGroup($fieldDefinitionMock)
        );
    }

    public function testUsesIdentifierIfNoTranslation(): void
    {
        $this->getTranslatorMock()
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        $list = $this->getArrayTranslatorFieldsGroupsList();

        self::assertEquals(
            [
                self::FIRST_GROUP_ID => self::FIRST_GROUP_ID,
                self::SECOND_GROUP_ID => self::SECOND_GROUP_ID,
            ],
            $list->getGroups()
        );
    }

    private function getArrayTranslatorFieldsGroupsList(
        array $groups = self::ALL_GROUPS_IDS,
        string $default = self::DEFAULT_GROUP_ID
    ): ArrayTranslatorFieldsGroupsList {
        return new ArrayTranslatorFieldsGroupsList(
            $this->getTranslatorMock(),
            $default,
            $groups
        );
    }

    /**
     * @return \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getTranslatorMock(): MockObject
    {
        if ($this->translatorMock === null) {
            $this->translatorMock = $this->createMock(TranslatorInterface::class);
        }

        return $this->translatorMock;
    }

    private function applyTranslationsForTranslationsMock(): void
    {
        $this->getTranslatorMock()
            ->expects($this->any())
            ->method('trans')
            ->will(
                $this->returnValueMap([
                    [self::FIRST_GROUP_ID, [], 'ezplatform_fields_groups', null, self::FIRST_GROUP_NAME],
                    [self::SECOND_GROUP_ID, [], 'ezplatform_fields_groups', null, self::SECOND_GROUP_NAME],
                ])
            );
    }

    /**
     * @param array $constructorArgs
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getFieldDefinitionMock(array $constructorArgs = []): MockObject
    {
        return $this
            ->getMockBuilder(FieldDefinition::class)
            ->setConstructorArgs($constructorArgs)
            ->getMockForAbstractClass();
    }
}
