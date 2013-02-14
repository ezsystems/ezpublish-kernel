<?php
/**
 * File containing the SelectionTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection as SelectionConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Selection converter in Legacy storage
 */
class SelectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new SelectionConverter;
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageValue
     */
    public function testToStorageValue()
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = array( 1, 3 );
        $fieldValue->sortKey = '1-3';

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = '1-3';
        $expectedStorageFieldValue->sortKeyString = '1-3';

        $actualStorageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue( $fieldValue, $actualStorageFieldValue );

        $this->assertEquals(
            $expectedStorageFieldValue,
            $actualStorageFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageValue
     */
    public function testToStorageValueEmpty()
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = array();
        $fieldValue->sortKey = '';

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = '';
        $expectedStorageFieldValue->sortKeyString = '';

        $actualStorageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue( $fieldValue, $actualStorageFieldValue );

        $this->assertEquals(
            $expectedStorageFieldValue,
            $actualStorageFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = '1-3';
        $storageFieldValue->sortKeyString = '1-3';

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = array( 1, 3 );
        $expectedFieldValue->sortKey = '1-3';

        $actualFieldValue = new FieldValue();

        $this->converter->toFieldValue( $storageFieldValue, $actualFieldValue );

        $this->assertEquals(
            $expectedFieldValue,
            $actualFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldValue
     */
    public function testToFieldValueEmpty()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = '';
        $storageFieldValue->sortKeyString = '';

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = array();
        $expectedFieldValue->sortKey = '';

        $actualFieldValue = new FieldValue();

        $this->converter->toFieldValue( $storageFieldValue, $actualFieldValue );

        $this->assertEquals(
            $expectedFieldValue,
            $actualFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    array(
                        'fieldSettings' => new FieldSettings(
                            array(
                                'isMultiple' => true,
                                'options' => array(
                                    0 => 'First',
                                    1 => 'Second',
                                    2 => 'Third'
                                )
                            )
                        )
                    )
                )
            )
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataInt1 = 1;
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection><options><option id="0" name="First"/><option id="1" name="Second"/><option id="2" name="Third"/></options></ezselection>

EOT;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition( $fieldDefinition, $actualStorageFieldDefinition );

        $this->assertEquals( $expectedStorageFieldDefinition, $actualStorageFieldDefinition );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionSingle()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    array(
                        'fieldSettings' => new FieldSettings(
                            array(
                                'isMultiple' => false,
                                'options' => array(
                                    0 => 'First',
                                )
                            )
                        )
                    )
                )
            )
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataInt1 = 0;
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection><options><option id="0" name="First"/></options></ezselection>

EOT;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition( $fieldDefinition, $actualStorageFieldDefinition );

        $this->assertEquals( $expectedStorageFieldDefinition, $actualStorageFieldDefinition );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldDefinition
     */
    public function testToFieldDefinitionMultiple()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataInt1 = 1;
        $storageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection>
  <options>
    <option id="0" name="First"/>
    <option id="1" name="Second"/>
    <option id="2" name="Third"/>
  </options>
</ezselection>
EOT;

        $expectedFieldDefinition = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    array(
                        'fieldSettings' => new FieldSettings(
                            array(
                                'isMultiple' => true,
                                'options' => array(
                                    0 => 'First',
                                    1 => 'Second',
                                    2 => 'Third'
                                )
                            )
                        )
                    )
                ),
                'defaultValue' => new FieldValue( array( 'data' => array() ) )
            )
        );

        $actualFieldDefinition = new PersistenceFieldDefinition();

        $this->converter->toFieldDefinition( $storageFieldDefinition, $actualFieldDefinition );

        $this->assertEquals( $expectedFieldDefinition, $actualFieldDefinition );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldDefinition
     */
    public function testToFieldDefinitionSingle()
    {
    }
}
