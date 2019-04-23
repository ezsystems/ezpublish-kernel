<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\MapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
// Needed for $sortOrder and $sortField properties
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;

/**
 * Test case for Mapper.
 */
class MapperTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::createGroupFromCreateStruct
     */
    public function testCreateGroupFromCreateStruct()
    {
        $createStruct = $this->getGroupCreateStructFixture();

        $mapper = new Mapper($this->getConverterRegistryMock(), $this->getMaskGeneratorMock());

        $group = $mapper->createGroupFromCreateStruct($createStruct);

        $this->assertInstanceOf(
            Group::class,
            $group
        );
        $this->assertPropertiesCorrect(
            [
                'id' => null,
                'name' => [
                    'eng-GB' => 'Media',
                ],
                'description' => [],
                'identifier' => 'Media',
                'created' => 1032009743,
                'modified' => 1033922120,
                'creatorId' => 14,
                'modifierId' => 14,
            ],
            $group
        );
    }

    /**
     * Returns a GroupCreateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct
     */
    protected function getGroupCreateStructFixture()
    {
        $struct = new GroupCreateStruct();

        $struct->name = [
            'eng-GB' => 'Media',
        ];
        $struct->description = [];
        $struct->identifier = 'Media';
        $struct->created = 1032009743;
        $struct->modified = 1033922120;
        $struct->creatorId = 14;
        $struct->modifierId = 14;

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::createTypeFromCreateStruct
     */
    public function testTypeFromCreateStruct()
    {
        $struct = $this->getContentTypeCreateStructFixture();

        $mapper = new Mapper($this->getConverterRegistryMock(), $this->getMaskGeneratorMock());
        $type = $mapper->createTypeFromCreateStruct($struct);

        foreach ($struct as $propName => $propVal) {
            $this->assertEquals(
                $struct->$propName,
                $type->$propName,
                "Property \${$propName} not equal"
            );
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::createTypeFromUpdateStruct
     */
    public function testTypeFromUpdateStruct()
    {
        $struct = $this->getContentTypeUpdateStructFixture();

        $mapper = new Mapper($this->getConverterRegistryMock(), $this->getMaskGeneratorMock());
        $type = $mapper->createTypeFromUpdateStruct($struct);

        $this->assertStructsEqual(
            $struct,
            $type
        );
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    protected function getContentTypeCreateStructFixture()
    {
        // Taken from example DB
        $struct = new CreateStruct();
        $struct->name = [
            'eng-US' => 'Folder',
        ];
        $struct->status = 0;
        $struct->description = [];
        $struct->identifier = 'folder';
        $struct->created = 1024392098;
        $struct->modified = 1082454875;
        $struct->creatorId = 14;
        $struct->modifierId = 14;
        $struct->remoteId = 'a3d405b81be900468eb153d774f4f0d2';
        $struct->urlAliasSchema = '';
        $struct->nameSchema = '<short_name|name>';
        $struct->isContainer = true;
        $struct->initialLanguageId = 2;
        $struct->sortField = Location::SORT_FIELD_MODIFIED_SUBNODE;
        $struct->sortOrder = Location::SORT_ORDER_ASC;
        $struct->defaultAlwaysAvailable = true;

        $struct->groupIds = [
            1,
        ];

        $fieldDefName = new FieldDefinition();

        $fieldDefShortDescription = new FieldDefinition();

        $struct->fieldDefinitions = [
            $fieldDefName,
            $fieldDefShortDescription,
        ];

        return $struct;
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct
     */
    protected function getContentTypeUpdateStructFixture(): UpdateStruct
    {
        // Taken from example DB
        $struct = new UpdateStruct();
        $struct->name = [
            'eng-US' => 'Folder',
        ];
        $struct->description = [];
        $struct->identifier = 'folder';
        $struct->modified = 1082454875;
        $struct->modifierId = 14;
        $struct->remoteId = md5(microtime() . uniqid());
        $struct->urlAliasSchema = '';
        $struct->nameSchema = '<short_name|name>';
        $struct->isContainer = true;
        $struct->initialLanguageId = 2;
        $struct->sortField = Location::SORT_FIELD_MODIFIED_SUBNODE;
        $struct->sortOrder = Location::SORT_ORDER_ASC;
        $struct->defaultAlwaysAvailable = true;

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::createCreateStructFromType
     */
    public function testCreateStructFromType()
    {
        $type = $this->getContentTypeFixture();

        $mapper = new Mapper($this->getConverterRegistryMock(), $this->getMaskGeneratorMock());
        $struct = $mapper->createCreateStructFromType($type);

        // Iterate through struct, since it has fewer props
        foreach ($struct as $propName => $propVal) {
            $this->assertEquals(
                $struct->$propName,
                $type->$propName,
                "Property \${$propName} not equal"
            );
        }
    }

    /**
     * Returns a Type fixture.
     *
     * @return Type
     */
    protected function getContentTypeFixture()
    {
        // Taken from example DB
        $type = new Type();
        $type->id = 23;
        $type->name = [
            'eng-US' => 'Folder',
        ];
        $type->status = 0;
        $type->description = [];
        $type->identifier = 'folder';
        $type->created = 1024392098;
        $type->modified = 1082454875;
        $type->creatorId = 14;
        $type->modifierId = 14;
        $type->remoteId = 'a3d405b81be900468eb153d774f4f0d2';
        $type->urlAliasSchema = '';
        $type->nameSchema = '<short_name|name>';
        $type->isContainer = true;
        $type->initialLanguageId = 2;
        $type->sortField = Location::SORT_FIELD_MODIFIED_SUBNODE;
        $type->sortOrder = Location::SORT_ORDER_ASC;
        $type->defaultAlwaysAvailable = true;
        $type->groupIds = [
            1,
        ];

        $fieldDefName = new FieldDefinition();
        $fieldDefName->id = 42;

        $fieldDefShortDescription = new FieldDefinition();
        $fieldDefName->id = 128;

        $type->fieldDefinitions = [
            $fieldDefName,
            $fieldDefShortDescription,
        ];

        return $type;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::extractGroupsFromRows
     */
    public function testExtractGroupsFromRows()
    {
        $rows = $this->getLoadGroupFixture();

        $mapper = new Mapper($this->getConverterRegistryMock(), $this->getMaskGeneratorMock());
        $groups = $mapper->extractGroupsFromRows($rows);

        $groupFixture = new Group();
        $groupFixture->created = 1032009743;
        $groupFixture->creatorId = 14;
        $groupFixture->id = 3;
        $groupFixture->modified = 1033922120;
        $groupFixture->modifierId = 14;
        $groupFixture->identifier = 'Media';

        $this->assertEquals(
            [$groupFixture],
            $groups
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::extractTypesFromRows
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::extractTypeFromRow
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::extractStorageFieldFromRow
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::extractFieldFromRow
     */
    public function testExtractTypesFromRowsSingle()
    {
        $rows = $this->getLoadTypeFixture();

        $mapper = $this->getNonConvertingMapper();
        $types = $mapper->extractTypesFromRows($rows);

        $this->assertEquals(
            1,
            count($types),
            'Incorrect number of types extracted'
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 1,
                'status' => 0,
                'name' => [
                    'eng-US' => 'Folder',
                ],
                'description' => [],
                'created' => 1024392098,
                'creatorId' => 14,
                'modified' => 1082454875,
                'modifierId' => 14,
                'identifier' => 'folder',
                'remoteId' => 'a3d405b81be900468eb153d774f4f0d2',
                'urlAliasSchema' => '',
                'nameSchema' => '<short_name|name>',
                'isContainer' => true,
                'initialLanguageId' => 2,
                'groupIds' => [1],
                'sortField' => 1,
                'sortOrder' => 1,
                'defaultAlwaysAvailable' => true,
            ],
            $types[0]
        );

        $this->assertEquals(
            6,
            count($types[0]->fieldDefinitions),
            'Incorrect number of field definitions'
        );
        $this->assertPropertiesCorrect(
            [
                'id' => 155,
                'name' => [
                    'eng-US' => 'Short name',
                ],
                'description' => [],
                'identifier' => 'short_name',
                'fieldGroup' => '',
                'fieldType' => 'ezstring',
                'isTranslatable' => true,
                'isRequired' => false,
                'isInfoCollector' => false,
                'isSearchable' => true,
                'position' => 2,
            ],
            $types[0]->fieldDefinitions[2]
        );

        $this->assertPropertiesCorrect(
            [
                'id' => 159,
                'name' => [],
                'description' => [],
                'identifier' => 'show_children',
                'fieldGroup' => '',
                'fieldType' => 'ezboolean',
                'isTranslatable' => false,
                'isRequired' => false,
                'isInfoCollector' => false,
                'isSearchable' => false,
                'position' => 6,
            ],
            $types[0]->fieldDefinitions[5]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::toStorageFieldDefinition
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $converterMock = $this->createMock(Converter::class);
        $converterMock->expects($this->once())
            ->method('toStorageFieldDefinition')
            ->with(
                $this->isInstanceOf(
                    FieldDefinition::class
                ),
                $this->isInstanceOf(
                    StorageFieldDefinition::class
                )
            );

        $converterRegistry = new ConverterRegistry(['some_type' => $converterMock]);

        $mapper = new Mapper($converterRegistry, $this->getMaskGeneratorMock());

        $fieldDef = new FieldDefinition();
        $fieldDef->fieldType = 'some_type';
        $fieldDef->name = [
            'eng-GB' => 'some name',
        ];

        $storageFieldDef = new StorageFieldDefinition();

        $mapper->toStorageFieldDefinition($fieldDef, $storageFieldDef);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper::toFieldDefinition
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $converterMock = $this->createMock(Converter::class);
        $converterMock->expects($this->once())
            ->method('toFieldDefinition')
            ->with(
                $this->isInstanceOf(
                    StorageFieldDefinition::class
                ),
                $this->isInstanceOf(
                    FieldDefinition::class
                )
            );

        $converterRegistry = new ConverterRegistry(['some_type' => $converterMock]);

        $mapper = new Mapper($converterRegistry, $this->getMaskGeneratorMock());

        $storageFieldDef = new StorageFieldDefinition();

        $fieldDef = new FieldDefinition();
        $fieldDef->fieldType = 'some_type';

        $mapper->toFieldDefinition($storageFieldDef, $fieldDef);
    }

    /**
     * Returns a Mapper with conversion methods mocked.
     *
     * @return Mapper
     */
    protected function getNonConvertingMapper()
    {
        $mapper = $this->getMockBuilder(Mapper::class)
            ->setMethods(array('toFieldDefinition'))
            ->setConstructorArgs(array($this->getConverterRegistryMock(), $this->getMaskGeneratorMock()))
            ->getMock();

        // Dedicatedly tested test
        $mapper->expects($this->atLeastOnce())
            ->method('toFieldDefinition')
            ->with(
                $this->isInstanceOf(
                    StorageFieldDefinition::class
                )
            )->will(
                $this->returnCallback(
                    function () {
                        return new FieldDefinition();
                    }
                )
            );

        return $mapper;
    }

    /**
     * Returns a converter registry mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface
     */
    protected function getConverterRegistryMock()
    {
        return $this->createMock(ConverterRegistryInterface::class);
    }

    /**
     * Returns fixture for {@link testExtractTypesFromRowsSingle()}.
     *
     * @return array
     */
    protected function getLoadTypeFixture()
    {
        return require __DIR__ . '/_fixtures/map_load_type.php';
    }

    /**
     * Returns fixture for {@link testExtractGroupsFromRows()}.
     *
     * @return array
     */
    protected function getLoadGroupFixture()
    {
        return require __DIR__ . '/_fixtures/map_load_group.php';
    }

    protected function getMaskGeneratorMock()
    {
        return $this->createMock(MaskGenerator::class);
    }
}
