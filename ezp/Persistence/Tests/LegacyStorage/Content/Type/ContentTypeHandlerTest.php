<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway;

/**
 * Test case for ContentTypeHandler.
 */
class ContentTypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::__construct
     */
    public function testCtor()
    {
        $gatewayMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway'
        );
        $handler = new ContentTypeHandler( $gatewayMock );

        $this->assertAttributeSame(
            $gatewayMock,
            'contentTypeGateway',
            $handler
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::create
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::typeFromCreateStruct
     */
    public function testCreate()
    {
        $createStructFix   = $this->getContenTypeCreateStructFixture();
        $createStructClone = clone $createStructFix;

        $gatewayMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway',
            array( 'insertType', 'insertGroupAssignement', 'insertFieldDefinition' )
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertType' )
            ->with( $this->equalTo( $createStructFix ) )
            ->will( $this->returnValue( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroupAssignement' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );
        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertFieldDefinition' )
            ->will( $this->returnValue( 42 ) );

        $handler = new ContentTypeHandler( $gatewayMock );
        $type = $handler->create( $createStructFix );

        $this->assertInstanceOf(
            'ezp\Persistence\Content\Type',
            $type,
            'Incorrect type returned from create()'
        );
        $this->assertEquals(
            23,
            $type->id,
            'Incorrect ID for Type.'
        );
        $this->assertPropertiesEqual(
            $createStructFix,
            $type,
            array(
                'name', 'description', 'identifier', 'created', 'modified',
                'creatorId', 'modifierId', 'remoteId', 'urlAliasSchema',
                'nameSchema', 'initialLanguageId', 'contentTypeGroupIds',
            )
        );

        $this->assertEquals(
            2,
            count( $type->fieldDefinitions ),
            'Incorrect number of field definitions'
        );

        $this->assertPropertiesEqual(
            $createStructFix->fieldDefinitions[0],
            $type->fieldDefinitions[0],
            array(
                'name', 'description', 'identifier', 'fieldGroup', 'position',
                'fieldType', 'translatable', 'required', 'isInfoCollector',
                'fieldTypeConstraints', 'defaultValue'
            )
        );
        $this->assertEquals(
            42,
            $type->fieldDefinitions[0]->id,
            'Field definition ID not set correctly'
        );

        $this->assertPropertiesEqual(
            $createStructFix->fieldDefinitions[1],
            $type->fieldDefinitions[1],
            array(
                'name', 'description', 'identifier', 'fieldGroup', 'position',
                'fieldType', 'translatable', 'required', 'isInfoCollector',
                'fieldTypeConstraints', 'defaultValue'
            )
        );
        $this->assertEquals(
            42,
            $type->fieldDefinitions[1]->id,
            'Field definition ID not set correctly'
        );

        $this->assertEquals(
            $createStructClone,
            $createStructFix,
            'Create struct manipulated'
        );
    }

    /**
     * Asserts that $properties of $expected and $actual are equal.
     *
     * @param object $expected
     * @param object $actual
     * @param array $properties
     * @return void
     */
    protected function assertPropertiesEqual( $expected, $actual, array $properties )
    {
        if ( !is_object( $expected ) )
        {
            throw new \InvalidArgumentException( 'First argument expected object.' );
        }
        if ( !is_object( $actual ) )
        {
            throw new \InvalidArgumentException( 'Second argument expected object.' );
        }

        foreach ( $properties as $propName )
        {
            $this->assertEquals(
                $expected->$propName,
                $actual->$propName,
                "Property \${$propName} not equal."
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
            'eng-US'           => 'Folder',
        );
        $struct->description = array(
            0                  => '',
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
        $fieldDefName->name = array(
            'always-available' => 'eng-US',
            'eng-US'           => 'Name',
        );
        $fieldDefName->description = array (
            0                  => '',
            'always-available' => false,
        );
        $fieldDefName->identifier = 'name';
        $fieldDefName->fieldGroup = '';
        $fieldDefName->position = 1;
        $fieldDefName->fieldType = 'ezstring';
        $fieldDefName->translatable = 1;
        $fieldDefName->required = 1;
        $fieldDefName->isInfoCollector = 0;
        // @todo: Got this field right?
        $fieldDefName->fieldTypeConstraints = array(
            'data_float1' => 0,
            'data_float2' => 0,
            'data_float3' => 0,
            'data_float4' => 0,
            'data_int1'   => 255,
            'data_int2'   => 0,
            'data_int3'   => 0,
            'data_int4'   => 0,
            'data_text1'  => 'Folder',
            'data_text2'  => '',
            'data_text3'  => '',
            'data_text4'  => '',
            'data_text5'  => '',
        );

        $fieldDefShortDescription = new FieldDefinition();
        $fieldDefShortDescription->name = array(
            'always-available' => 'eng-US',
            'eng-US'           => 'Short description',
        );
        $fieldDefShortDescription->description = array (
            0                  => '',
            'always-available' => false,
        );
        $fieldDefShortDescription->identifier = 'short_description';
        $fieldDefShortDescription->fieldGroup = '';
        $fieldDefShortDescription->position = 3;
        $fieldDefShortDescription->fieldType = 'ezxmltext';
        $fieldDefShortDescription->translatable = 1;
        $fieldDefShortDescription->required = 0;
        $fieldDefShortDescription->isInfoCollector = 0;
        // @todo: Got this field right?
        $fieldDefShortDescription->fieldTypeConstraints = array(
            'data_float1' => 0,
            'data_float2' => 0,
            'data_float3' => 0,
            'data_float4' => 0,
            'data_int1'   => 5,
            'data_int2'   => 0,
            'data_int3'   => 0,
            'data_int4'   => 0,
            'data_text1'  => '',
            'data_text2'  => '',
            'data_text3'  => '',
            'data_text4'  => '',
            'data_text5'  => '',
        );

        // 3 more …

        $struct->fieldDefinitions = array(
            $fieldDefName,
            $fieldDefShortDescription
        );

        return $struct;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
