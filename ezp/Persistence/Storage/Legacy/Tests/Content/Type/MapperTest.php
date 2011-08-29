<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\MapperTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Type\Mapper,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,

    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,

    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;

/**
 * Test case for Mapper.
 */
class MapperTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::createGroupFromCreateStruct
     */
    public function testCreateGroupFromCreateStruct()
    {
        $createStruct = $this->getGroupCreateStructFixture();

        $mapper = new Mapper( $this->getConverterRegistryMock() );

        $group = $mapper->createGroupFromCreateStruct( $createStruct );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type\\Group',
            $group
        );
        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'name' => array(
                    'always-available' => 'eng-GB',
                    'eng-GB' => 'Media',
                ),
                'description' => array(),
                'identifier' => 'Media',
                'created' => 1032009743,
                'modified' => 1033922120,
                'creatorId' => 14,
                'modifierId' => 14,
            ),
            $group
        );
    }

    /**
     * Returns a GroupCreateStruct fixture.
     *
     * @return \ezp\Persistence\Content\Type\Group\CreateStruct
     */
    protected function getGroupCreateStructFixture()
    {
        $struct = new GroupCreateStruct();

        $struct->name = array(
            'always-available' => 'eng-GB',
            'eng-GB' => 'Media',
        );
        $struct->description = array(
            'always-available' => 'eng-GB',
            'eng-GB' => '',
        );
        $struct->identifier = 'Media';
        $struct->created = 1032009743;
        $struct->modified = 1033922120;
        $struct->creatorId = 14;
        $struct->modifierId = 14;

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::createTypeFromCreateStruct
     */
    public function testTypeFromCreateStruct()
    {
        $struct = $this->getContenTypeCreateStructFixture();

        $mapper = new Mapper( $this->getConverterRegistryMock() );
        $type = $mapper->createTypeFromCreateStruct( $struct );

        foreach ( $struct as $propName => $propVal )
        {
            $this->assertEquals(
                $struct->$propName,
                $type->$propName,
                "Property \${$propName} not equal"
            );
        }
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \ezp\Persistence\Content\Type\CreateStruct
     */
    protected function getContenTypeCreateStructFixture()
    {
        // Taken from example DB
        $struct = new CreateStruct();
        $struct->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Folder',
        );
        $struct->status = 0;
        $struct->description = array(
            0 => '',
            'always-available' => false,
        );
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
        $struct->groupIds = array(
            1,
        );

        $fieldDefName = new FieldDefinition();

        $fieldDefShortDescription = new FieldDefinition();

        $struct->fieldDefinitions = array(
            $fieldDefName,
            $fieldDefShortDescription
        );

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::createCreateStructFromType
     */
    public function testCreateStructFromType()
    {
        $type = $this->getContenTypeFixture();

        $mapper = new Mapper( $this->getConverterRegistryMock() );
        $struct = $mapper->createCreateStructFromType( $type );

        // Iterate through struct, since it has fewer props
        foreach ( $struct as $propName => $propVal )
        {
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
    protected function getContenTypeFixture()
    {
        // Taken from example DB
        $type = new Type();
        $type->id = 23;
        $type->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Folder',
        );
        $type->status = 0;
        $type->description = array(
            0 => '',
            'always-available' => false,
        );
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
        $type->groupIds = array(
            1,
        );

        $fieldDefName = new FieldDefinition();
        $fieldDefName->id = 42;

        $fieldDefShortDescription = new FieldDefinition();
        $fieldDefName->id = 128;

        $type->fieldDefinitions = array(
            $fieldDefName,
            $fieldDefShortDescription
        );

        return $type;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::extractGroupsFromRows
     */
    public function testExtractGroupsFromRows()
    {
        $rows = $this->getLoadGroupFixture();

        $mapper = new Mapper( $this->getConverterRegistryMock() );
        $groups = $mapper->extractGroupsFromRows( $rows );

        $groupFixture = new Group();
        $groupFixture->created    = 1032009743;
        $groupFixture->creatorId  = 14;
        $groupFixture->id         = 3;
        $groupFixture->modified   = 1033922120;
        $groupFixture->modifierId = 14;
        $groupFixture->identifier = 'Media';

        $this->assertEquals(
            array( $groupFixture ),
            $groups
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::extractTypesFromRows
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::extractTypeFromRow
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::extractFieldFromRow
     */
    public function testExtractTypesFromRowsSingle()
    {
        $rows = $this->getLoadTypeFixture();

        $mapper = $this->getNonConvertingMapper();
        $types = $mapper->extractTypesFromRows( $rows );

        $this->assertEquals(
            1,
            count( $types ),
            'Incorrect number of types extracted'
        );

        $this->assertPropertiesCorrect(
            // "random" sample
            array(
                'id' => 1,
                'status' => 0,
                'name' => array(
                    'always-available' => 'eng-US',
                    'eng-US'           => 'Folder'
                ),
                'description' => array(
                    0                  => '',
                    'always-available' => false,
                ),
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
                'groupIds' => array( 1 ),
            ),
            $types[0]
        );

        // "random" sample
        $this->assertEquals(
            5,
            count( $types[0]->fieldDefinitions ),
            'Incorrect number of field definitions'
        );
        $this->assertPropertiesCorrect(
            // "random" sample
            array(
                'id' => 155,
                'fieldType' => 'ezstring',
                'identifier' => 'short_name',
                'isInfoCollector' => false,
                'isRequired' => false,
            ),
            $types[0]->fieldDefinitions[2]
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $converterMock = $this->getMockForAbstractClass(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
        );
        $converterMock->expects( $this->once() )
            ->method( 'toStorageFieldDefinition' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition'
                )
            );

        $converterRegistry = new Converter\Registry();
        $converterRegistry->register( 'some_type', $converterMock );

        $mapper = new Mapper( $converterRegistry );

        $fieldDef = new FieldDefinition();
        $fieldDef->fieldType = 'some_type';

        $storageFieldDef = new StorageFieldDefinition();

        $mapper->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Mapper::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $converterMock = $this->getMockForAbstractClass(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
        );
        $converterMock->expects( $this->once() )
            ->method( 'toFieldDefinition' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\FieldDefinition'
                )
            );

        $converterRegistry = new Converter\Registry();
        $converterRegistry->register( 'some_type', $converterMock );

        $mapper = new Mapper( $converterRegistry );

        $storageFieldDef = new StorageFieldDefinition();

        $fieldDef = new FieldDefinition();
        $fieldDef->fieldType = 'some_type';

        $mapper->toFieldDefinition( $storageFieldDef, $fieldDef );
    }

    /**
     * Returns a Mapper with conversion methods mocked
     *
     * @return Mapper
     */
    protected function getNonConvertingMapper()
    {
        $mapper = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Mapper',
            array( 'toFieldDefinition' ),
            array( $this->getConverterRegistryMock() )
        );
        // Dedicatedly tested test
        $mapper->expects( $this->atLeastOnce() )
            ->method( 'toFieldDefinition' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition'
                )
            )->will(
                $this->returnCallback(
                    function ()
                    {
                        return new FieldDefinition();
                    }
                )
            );
        return $mapper;
    }

    /**
     * Returns a converter registry mock
     *
     * @return ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected function getConverterRegistryMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry'
        );
    }

    /**
     * Returns fixture for {@link testExtractTypesFromRowsSingle()}
     *
     * @return array
     */
    protected function getLoadTypeFixture()
    {
        return require __DIR__ . '/_fixtures/map_load_type.php';
    }

    /**
     * Returns fixture for {@link testExtractGroupsFromRows()}
     *
     * @return array
     */
    protected function getLoadGroupFixture()
    {
        return require __DIR__ . '/_fixtures/map_load_group.php';
    }
}
