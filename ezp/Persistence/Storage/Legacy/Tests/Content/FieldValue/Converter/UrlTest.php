<?php
/**
 * File containing the UrlTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\FieldValue\Converter;
use ezp\Content\FieldType\Url\Value as UrlValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Url as UrlConverter,
    ezp\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Url converter in Legacy storage
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Url
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new UrlConverter;
    }

    /**
     * @group fieldType
     * @group url
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Url::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $text = "eZ Systems";
        $value->data = new UrlValue( "http://ez.no/", $text );
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $text, $storageFieldValue->dataText );
    }

    /**
     * @group fieldType
     * @group url
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Url::toFieldValue
     */
    public function testToFieldValue()
    {
        $text = "A link's text";
        $urlId = 842;
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = $text;
        $storageFieldValue->dataInt = $urlId;
        $storageFieldValue->sortKeyString = false;
        $storageFieldValue->sortKeyInt = false;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Url\\Value", $fieldValue->data );
        self::assertFalse( $fieldValue->sortKey );
        self::assertSame( $text, $fieldValue->data->text );
        self::assertEquals( $urlId, $fieldValue->data->getState( "urlId" ) );
    }

    /**
     * @group fieldType
     * @group url
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Url::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition( new PersistenceFieldDefinition, new StorageFieldDefinition );
    }

    /**
     * @group fieldType
     * @group url
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Url::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition( new StorageFieldDefinition, new PersistenceFieldDefinition );
    }
}
