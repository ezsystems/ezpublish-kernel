<?php
/**
 * File containing the UrlTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url as UrlConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Url converter in Legacy storage
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $text = "eZ Systems";
        $value->data = array( "text" => $text );
        $value->externalData = "http://ez.no/";
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $text, $storageFieldValue->dataText );
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url::toFieldValue
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
        self::assertInternalType( "array", $fieldValue->data );
        self::assertFalse( $fieldValue->sortKey );
        self::assertSame( $text, $fieldValue->data["text"] );
        self::assertEquals( $urlId, $fieldValue->data["urlId"] );
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition( new PersistenceFieldDefinition, new StorageFieldDefinition );
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition( new StorageFieldDefinition, new PersistenceFieldDefinition );
    }
}
