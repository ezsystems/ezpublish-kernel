<?php
/**
 * File containing the KeywordTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Keyword as KeywordConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Keyword converter in Legacy storage
 */
class KeywordTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Keyword
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new KeywordConverter;
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Keyword::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = array( "key1", "key2" );
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        $this->assertNull( $storageFieldValue->dataText );
        $this->assertNull( $storageFieldValue->dataInt );
        $this->assertNull( $storageFieldValue->dataFloat );
        $this->assertEquals( 0, $storageFieldValue->sortKeyInt );
        $this->assertEquals( "", $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Keyword::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        $this->assertSame( array(), $fieldValue->data );
        $this->assertNull( $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Keyword::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition( new PersistenceFieldDefinition, new StorageFieldDefinition );
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Keyword::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition( new StorageFieldDefinition, new PersistenceFieldDefinition );
    }
}
