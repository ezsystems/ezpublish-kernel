<?php
/**
 * File containing the RatingTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating as RatingConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Rating converter in Legacy storage
 */
class RatingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new RatingConverter;
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = false;
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( null, $storageFieldValue->dataInt );
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating::toStorageValue
     */
    public function testToStorageValueDisabled()
    {
        $value = new FieldValue;
        $value->data = true;
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( 1, $storageFieldValue->dataInt );
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = null;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( false, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating::toFieldValue
     */
    public function testToFieldValueDisabled()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = 1;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( true, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition( new PersistenceFieldDefinition, new StorageFieldDefinition );
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Rating::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition( new StorageFieldDefinition, new PersistenceFieldDefinition );
    }
}
