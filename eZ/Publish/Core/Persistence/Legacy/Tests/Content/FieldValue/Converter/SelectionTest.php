<?php
/**
 * File containing the SelectionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;
use eZ\Publish\Core\Repository\FieldType\Selection\Value as SelectionValue,
    eZ\Publish\Core\Repository\FieldType\FieldSettings,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection as SelectionConverter,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase,
    DOMDocument,
    DOMXPath;

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

    protected function assertXpathMatch( $expected, $xpath, DOMDocument $doc, $message = null )
    {
        $xpathObj = new DOMXPath( $doc );

        $this->assertEquals(
            $expected,
            $xpathObj->evaluate( $xpath ),
            $message
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = array( "Choice1", "Choice2" );
        $value->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => true,
                "options" => array(
                    "Choice0",
                    "Choice1",
                    "Choice2",
                    "Choice3",
                ),
            )
        );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        $this->assertSame( "1-2", $storageFieldValue->dataText );
        $this->assertSame( "1-2", $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldValue
     */
    public function testToFieldValueMultiple()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = "0-1-3";
        $storageFieldValue->sortKeyString = "0-1-3";
        $fieldValue = new FieldValue;
        $fieldValue->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => true,
                "options" => array(
                    "Choice0",
                    "Choice1",
                    "Choice2",
                    "Choice3",
                ),
            )
        );

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        $this->assertEquals(
            array( "Choice0", "Choice1", "Choice3" ),
            $fieldValue->data
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = "0";
        $storageFieldValue->sortKeyString = "0";
        $fieldValue = new FieldValue;
        $fieldValue->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => false,
                "options" => array(
                    "Choice0",
                    "Choice1",
                    "Choice2",
                    "Choice3",
                ),
            )
        );

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        $this->assertEquals(
            array( "Choice0" ),
            $fieldValue->data
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldValue
     */
    public function testToFieldValueEmpty()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = "";
        $storageFieldValue->sortKeyString = "";
        $fieldValue = new FieldValue;
        $fieldValue->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => true,
                "options" => array(
                    "Choice0",
                    "Choice1",
                    "Choice2",
                    "Choice3",
                ),
            )
        );

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        $this->assertEquals(
            array(),
            $fieldValue->data
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => true,
                "options" => array(
                    "Choice1",
                    "Choice2",
                    "Choice3",
                ),
            )
        );

        $storageFieldDef = new StorageFieldDefinition;
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(
                array(
                    "fieldTypeConstraints" => $fieldTypeConstraints,
                )
            ),
            $storageFieldDef
        );
        self::assertSame(
            1,
            $storageFieldDef->dataInt1
        );
        $dataText5 = new DOMDocument;
        $dataText5->loadXML( $storageFieldDef->dataText5 );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options)",
            $dataText5
        );
        self::assertXpathMatch(
            3,
            "count(/ezselection/options/option)",
            $dataText5
        );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options/option[@id=0 and @name='Choice1'])",
            $dataText5
        );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options/option[@id=1 and @name='Choice2'])",
            $dataText5
        );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options/option[@id=2 and @name='Choice3'])",
            $dataText5
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionSingle()
    {
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => false,
                "options" => array(
                    "Choice1",
                    "Choice2",
                    "Choice3",
                ),
            )
        );

        $storageFieldDef = new StorageFieldDefinition;
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(
                array(
                    "fieldTypeConstraints" => $fieldTypeConstraints,
                )
            ),
            $storageFieldDef
        );
        self::assertSame(
            0,
            $storageFieldDef->dataInt1
        );
        $dataText5 = new DOMDocument;
        $dataText5->loadXML( $storageFieldDef->dataText5 );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options)",
            $dataText5
        );
        self::assertXpathMatch(
            3,
            "count(/ezselection/options/option)",
            $dataText5
        );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options/option[@id=0 and @name='Choice1'])",
            $dataText5
        );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options/option[@id=1 and @name='Choice2'])",
            $dataText5
        );
        self::assertXpathMatch(
            1,
            "count(/ezselection/options/option[@id=2 and @name='Choice3'])",
            $dataText5
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldDefinition
     */
    public function testToFieldDefinitionMultiple()
    {
        $fieldDef = new PersistenceFieldDefinition;

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                array(
                    "dataInt1" => 1,
                    "dataText5" => '<?xml version="1.0" encoding="utf-8"?><ezselection><options><option id="0" name="Choice1"/><option id="1" name="Choice2"/><option id="2" name="Choice3"/></options></ezselection>'
                )
            ),
            $fieldDef
        );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertTrue(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
        self::assertSame(
            array(
                "Choice1",
                "Choice2",
                "Choice3",
            ),
            $fieldDef->fieldTypeConstraints->fieldSettings["options"]
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Selection::toFieldDefinition
     */
    public function testToFieldDefinitionSingle()
    {
        $fieldDef = new PersistenceFieldDefinition;

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                array(
                    "dataInt1" => 0,
                    "dataText5" => '<?xml version="1.0" encoding="utf-8"?><ezselection><options><option id="0" name="Choice1"/><option id="1" name="Choice2"/><option id="2" name="Choice3"/></options></ezselection>'
                )
            ),
            $fieldDef
        );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertFalse(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
        self::assertSame(
            array(
                "Choice1",
                "Choice2",
                "Choice3",
            ),
            $fieldDef->fieldTypeConstraints->fieldSettings["options"]
        );
    }
}
