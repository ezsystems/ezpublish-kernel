<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\MapperTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type;
use ezp\Persistence\LegacyStorage\Content\Type\Mapper,

    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,

    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\GroupCreateStruct;

/**
 * Test case for Mapper.
 */
class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Mapper::createGroupFromCreateStruct
     */
    public function testCreateGroupFromCreateStruct()
    {
        $createStruct = $this->getGroupCreateStructFixture();

        $mapper = new Mapper();

        $group = $mapper->createGroupFromCreateStruct( $createStruct );

        $this->assertInstanceOf(
            'ezp\Persistence\Content\Type\Group',
            $group
        );
        $this->assertPropertiesCorrect(
            array(
                'id'   => null,
                'name' => array(
                    'always-available' => 'eng-GB',
                    'eng-GB' => 'Media',
                ),
                'description' => null,
                'identifier' => 'Media',
                'created'    => 1032009743,
                'modified'   => 1033922120,
                'creatorId'  => 14,
                'modifierId' => 14,
            ),
            $group
        );
    }

    /**
     * Returns a GroupCreateStruct fixture.
     *
     * @return GroupCreateStruct
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
        $struct->created    = 1032009743;
        $struct->modified   = 1033922120;
        $struct->creatorId  = 14;
        $struct->modifierId = 14;

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Mapper::createTypeFromCreateStruct
     */
    public function testTypeFromCreateStruct()
    {
        $struct = $this->getContenTypeCreateStructFixture();

        $mapper = new Mapper();
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
     * Returns a ContentTypeCreateStruct fixture.
     *
     * @return ContentTypeCreateStruct
     */
    protected function getContenTypeCreateStructFixture()
    {
        // Taken from example DB
        $struct = new ContentTypeCreateStruct();
        $struct->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Folder',
        );
        $struct->version = 0;
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
        $struct->contentTypeGroupIds = array(
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Mapper::extractTypesFromRows
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Mapper::extractTypeFromRow
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Mapper::extractFieldFromRow
     */
    public function testExtractTypesFromRowsSingle()
    {
        $rows = $this->getLoadTypeFixture();

        $mapper = new Mapper();
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
                'version' => 0,
                'created' => 1024392098,
                'creatorId' => 14,
                'modified' => 1082454875,
                'modifierId' => 14,
                'identifier' => 'folder',
                'isContainer' => true,
                'contentTypeGroupIds' => array( 1 ),
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

    protected function assertPropertiesCorrect( array $properties, $object )
    {
        if ( !is_object( $object ) )
        {
            throw new \InvalidArgumentException(
                'Expected object as second parameter, received ' . gettype( $object )
            );
        }
        foreach ( $properties as $propName => $propVal )
        {
            $this->assertSame(
                $propVal,
                $object->$propName,
                "Incorrect value for \${$propName}"
            );
        }
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
}
