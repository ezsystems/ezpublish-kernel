<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\MapperTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content\Mapper,
    ezp\Persistence\LegacyStorage\Content\FieldValueConverter,
    ezp\Persistence\LegacyStorage\Content\FieldValueConverterRegistry,
    ezp\Persistence\LegacyStorage\Content\StorageFieldValue,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\ContentCreateStruct;

/**
 * Test case for Mapper
 */
class MapperTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Mapper::__construct
     */
    public function testCtor()
    {
        $regMock = $this->getValueConverterRegistryMock();

        $mapper = new Mapper( $regMock );

        $this->assertAttributeSame(
            $regMock,
            'converterRegistry',
            $mapper
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Mapper::createContentFromCreateStruct
     */
    public function testCreateContentFromCreateStruct()
    {
        $struct = $this->getCreateStructFixture();

        $mapper = new Mapper( $this->getValueConverterRegistryMock() );
        $content = $mapper->createContentFromCreateStruct( $struct );

        $this->assertStructsEqual(
            $struct,
            $content,
            array( 'name', 'typeId', 'sectionId', 'ownerId' )
        );
    }

    /**
     * Returns a ContentCreateStruct fixture
     *
     * @return ContentCreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new ContentCreateStruct();

        $struct->name            = 'Content name';
        $struct->typeId          = 23;
        $struct->sectionId       = 42;
        $struct->ownerId         = 13;
        $struct->parentLocations = array( 2, 3, 4, );
        $struct->fields          = array( new Field(), );

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Mapper::createVersionForContent
     */
    public function testCreateVersionFromContent()
    {
        $content = $this->getContentFixture();

        $mapper = new Mapper( $this->getValueConverterRegistryMock() );
        $version = $mapper->createVersionForContent( $content, 1 );

        $this->assertPropertiesCorrect(
            array(
                'id'        => null,
                'versionNo' => 1,
                'creatorId' => 13,
                'state'     => 0,
                'contentId' => 2342,
                'fields'    => array(),
            ),
            $version
        );

        $this->assertAttributeGreaterThanOrEqual(
            time() - 1000,
            'created',
            $version
        );
        $this->assertAttributeGreaterThanOrEqual(
            time() - 1000,
            'modified',
            $version
        );
    }

    /**
     * Returns a Content fixture
     *
     * @return Content
     */
    protected function getContentFixture()
    {
        $struct = new Content();

        $struct->id              = 2342;
        $struct->name            = 'Content name';
        $struct->typeId          = 23;
        $struct->sectionId       = 42;
        $struct->ownerId         = 13;
        $struct->versionInfos    = array();
        $struct->locations       = array();

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Mapper::convertToStorageValue;
     */
    public function testConvertToStorageValue()
    {
        $convMock = $this->getMock(
            'ezp\\Persistence\\LegacyStorage\\Content\\FieldValueConverter'
        );
        $convMock->expects( $this->once() )
            ->method( 'toStorage' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\FieldValue'
                )
            )->will( $this->returnValue( new StorageFieldValue() ) );

        $reg = new FieldValueConverterRegistry();
        $reg->register( 'some-type', $convMock );

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $mapper = new Mapper( $reg );
        $res = $mapper->convertToStorageValue( $field );

        $this->assertInstanceOf(
            'ezp\\Persistence\\LegacyStorage\\Content\\StorageFieldValue',
            $res
        );
    }

    /**
     * Returns a FieldValue converter registry mock
     *
     * @return FieldValueConverterRegistry
     */
    protected function getValueConverterRegistryMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\LegacyStorage\\Content\FieldValueConverterRegistry'
        );
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
