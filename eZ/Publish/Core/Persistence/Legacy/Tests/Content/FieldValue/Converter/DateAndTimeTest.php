<?php
/**
 * File containing the DateAndTimeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;
use eZ\Publish\Core\FieldType\DateAndTime\Value as DateAndTimeValue,
    eZ\Publish\Core\FieldType\DateAndTime\Type as DateAndTimeType,
    eZ\Publish\Core\FieldType\FieldSettings,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime as DateAndTimeConverter,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase,
    DateTime,
    DateInterval,
    SimpleXMLElement,
    DOMDocument,
    ReflectionObject;

/**
 * Test case for DateAndTime converter in Legacy storage
 */
class DateAndTimeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime
     */
    protected $converter;

    /**
     * @var \DateTime
     */
    protected $date;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new DateAndTimeConverter;
        $this->date = new DateTime( '@1048633200' );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = $this->date;
        $value->sortKey = array( 'sort_key_int' => $this->date->getTimestamp() );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data->getTimestamp(), $storageFieldValue->dataInt );
        self::assertSame( $value->sortKey['sort_key_int'], $storageFieldValue->sortKeyInt );
        self::assertSame( '', $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = $this->date->getTimestamp();
        $storageFieldValue->sortKeyString = '';
        $storageFieldValue->sortKeyInt = $this->date->getTimestamp();
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( 'DateTime', $fieldValue->data );
        self::assertSame( $storageFieldValue->dataInt, $fieldValue->data->getTimestamp() );
        self::assertSame( $storageFieldValue->sortKeyInt, $fieldValue->sortKey['sort_key_int'] );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionWithAdjustment()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $dateInterval = DateInterval::createFromDateString( '+10 years, -1 month, +3 days, -13 hours' );
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'useSeconds' => true,
                'defaultType' => DateAndTimeType::DEFAULT_CURRENT_DATE_ADJUSTED,
                'dateInterval' => $dateInterval
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            DateAndTimeType::DEFAULT_CURRENT_DATE_ADJUSTED,
            $storageFieldDef->dataInt1
        );
        self::assertSame(
            1,
            $storageFieldDef->dataInt2
        );

        $xml = new SimpleXMLElement( $storageFieldDef->dataText5 );
        foreach ( $this->getXMLToDateIntervalMap() as $xmlNode => $property )
        {
            self::assertSame(
                $dateInterval->format( "%$property" ),
                (string)$xml->{$xmlNode}['value']
            );
        }
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionNoDefault()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'useSeconds' => true,
                'defaultType' => DateAndTimeType::DEFAULT_EMPTY,
                'dateInterval' => null
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            DateAndTimeType::DEFAULT_EMPTY,
            $storageFieldDef->dataInt1
        );
        self::assertSame(
            1,
            $storageFieldDef->dataInt2
        );
        self::assertNull( $storageFieldDef->dataText5 );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionCurrentDate()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'useSeconds' => true,
                'defaultType' => DateAndTimeType::DEFAULT_CURRENT_DATE,
                'dateInterval' => null
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            DateAndTimeType::DEFAULT_CURRENT_DATE,
            $storageFieldDef->dataInt1
        );
        self::assertSame(
            1,
            $storageFieldDef->dataInt2
        );
        self::assertNull( $storageFieldDef->dataText5 );
    }

    /**
     * Returns map from internal XML nodes to DateInterval properties for date adjustment
     *
     * @return array Key is the XML node name, value is the DateInterval property
     */
    private function getXMLToDateIntervalMap()
    {
        return array(
            'year' => 'y',
            'month' => 'm',
            'day' => 'd',
            'hour' => 'h',
            'minute' => 'i',
            'second' => 's'
        );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toFieldDefinition
     */
    public function testToFieldDefinitionNoDefault()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => DateAndTimeType::DEFAULT_EMPTY,
                'dataInt2' => 1
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertNull( $fieldDef->defaultValue->data );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toFieldDefinition
     */
    public function testToFieldDefinitionCurrentDate()
    {
        $time = time();
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => DateAndTimeType::DEFAULT_CURRENT_DATE,
                'dataInt2' => 1
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertInstanceOf( 'DateTime', $fieldDef->defaultValue->data );
        self::assertGreaterThanOrEqual( $time, $fieldDef->defaultValue->data->getTimestamp() );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toFieldDefinition
     */
    public function testToFieldDefinitionWithAdjustmentAndSeconds()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $dateInterval = DateInterval::createFromDateString( '2 years, 1 month, -4 days, 2 hours, 0 minute, 34 seconds' );
        $date = new DateTime;
        $date->add( $dateInterval );
        $timestamp = $date->getTimestamp();

        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => DateAndTimeType::DEFAULT_CURRENT_DATE_ADJUSTED,
                'dataInt2' => 1,
                'dataText5' => $this->getXMLStringFromDateInterval( $dateInterval )
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertInstanceOf( 'DateTime', $fieldDef->defaultValue->data );
        $generatedTimestamp = $fieldDef->defaultValue->data->getTimestamp();
        self::assertGreaterThanOrEqual( $timestamp, $generatedTimestamp );
        // Giving a margin of 1 second for test execution
        self::assertLessThanOrEqual( $timestamp + 1, $generatedTimestamp );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::toFieldDefinition
     */
    public function testToFieldDefinitionWithAdjustmentNoSeconds()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $seconds = 34;
        $dateInterval = DateInterval::createFromDateString( "2 years, 1 month, -4 days, 2 hours, 0 minute, $seconds seconds" );
        $date = new DateTime;
        $date->add( $dateInterval );
        // Removing $seconds as they're not supposed to be taken into account
        $timestamp = $date->getTimestamp() - $seconds;

        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => DateAndTimeType::DEFAULT_CURRENT_DATE_ADJUSTED,
                'dataInt2' => 0,
                'dataText5' => $this->getXMLStringFromDateInterval( $dateInterval )
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertInstanceOf( 'DateTime', $fieldDef->defaultValue->data );
        $generatedTimestamp = $fieldDef->defaultValue->data->getTimestamp();
        self::assertGreaterThanOrEqual( $timestamp, $generatedTimestamp );
        // Giving a margin of 1 second for test execution
        self::assertLessThanOrEqual( $timestamp + 1, $generatedTimestamp );
    }

    /**
     * Generates XML string from $dateInterval
     *
     * @param \DateInterval $dateInterval
     * @return string
     */
    private function getXMLStringFromDateInterval( DateInterval $dateInterval )
    {
        $xmlString = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<adjustment>
    <year value="$dateInterval->y"/>
    <month value="$dateInterval->m"/>
    <day value="$dateInterval->d"/>
    <hour value="$dateInterval->h"/>
    <minute value="$dateInterval->i"/>
    <second value="$dateInterval->s"/>
</adjustment>
EOT;
        return $xmlString;
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::getDateIntervalFromXML
     */
    public function testGetDateIntervalFromXML()
    {
        $dateIntervalReference = DateInterval::createFromDateString( '2 years, 1 month, -4 days, 2 hours, 0 minute, 34 seconds' );

        $refConverter = new ReflectionObject( $this->converter );
        $refMethod = $refConverter->getMethod( 'getDateIntervalFromXML' );
        $refMethod->setAccessible( true );
        $generatedDateInterval = $refMethod->invoke(
            $this->converter,
            $this->getXMLStringFromDateInterval( $dateIntervalReference )
        );
        self::assertEquals( $dateIntervalReference, $generatedDateInterval );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime::generateDateIntervalXML
     */
    public function testGenerateDateIntervalXML()
    {
        $dateIntervalReference = DateInterval::createFromDateString( '2 years, 1 month, -4 days, 2 hours, 0 minute, 34 seconds' );
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML( $this->getXMLStringFromDateInterval( $dateIntervalReference ) );

        $refConverter = new ReflectionObject( $this->converter );
        $refMethod = $refConverter->getMethod( 'generateDateIntervalXML' );
        $refMethod->setAccessible( true );
        self::assertEquals(
            $dom->saveXML(),
            $refMethod->invoke( $this->converter, $dateIntervalReference )
        );
    }
}
