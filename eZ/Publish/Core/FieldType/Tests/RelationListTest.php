<?php
/**
 * File containing the RelationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\RelationList\Type as Relation,
    eZ\Publish\Core\FieldType\RelationList\Value,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    PHPUnit_Framework_TestCase,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo;

class RelationListTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new Relation( $this->validatorService, $this->fieldTypeTools );
        self::assertEmpty(
            $ft->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = new Relation( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(
                    'selectionMethod' => array(
                    'type' => 'int',
                    'default' => Relation::SELECTION_BROWSE,
                ),
                    'selectionRoot' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Relation( $this->validatorService, $this->fieldTypeTools );
        $ft->acceptValue( new Value( array( null ) ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Relation( $this->validatorService, $this->fieldTypeTools );
        $value = new Value( array( 1 ) );
        self::assertSame( $value, $ft->acceptValue( $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new Relation( $this->validatorService, $this->fieldTypeTools );
        $fieldValue = $ft->toPersistenceValue( new Value( array( 1 ) ) );

        self::assertSame( array( "destinationContentIds" => array( 1 ) ), $fieldValue->data );
        self::assertSame( null, $fieldValue->externalData );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::fromPersistenceValue
     */
    public function testFromPersistenceValue()
    {
        $expectedValue = new Value( array( 1 ) );

        $fieldValue = new FieldValue();
        $fieldValue->data = array( "destinationContentIds" => array( 1 ) );
        $fieldValue->externalData = null;

        $ft = new Relation( $this->validatorService, $this->fieldTypeTools );
        $value = $ft->fromPersistenceValue( $fieldValue );

        self::assertEquals( $expectedValue, $value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::acceptValue
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::__construct
     */
    public function testBuildValueWithContentInfo()
    {
        $type = new Relation( $this->validatorService, $this->fieldTypeTools );
        $contentInfo = new ContentInfo( array( 'id' => 1 ) );
        $value = $type->acceptValue( $contentInfo );
        self::assertSame( array( $contentInfo->id ), $value->destinationContentIds );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::acceptValue
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::__construct
     */
    public function testBuildValueWithId()
    {
        $type = new Relation( $this->validatorService, $this->fieldTypeTools );
        $contentId = 1;
        $value = $type->acceptValue( $contentId );
        self::assertSame( array( $contentId ), $value->destinationContentIds );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::acceptValue
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::__construct
     */
    public function testBuildValueWithIdList()
    {
        $type = new Relation( $this->validatorService, $this->fieldTypeTools );
        $contentId = 1;
        $value = $type->acceptValue( array( $contentId ) );
        self::assertSame( array( $contentId ), $value->destinationContentIds );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Value::__construct
     */
    public function testValueConstructor()
    {
        $contentId = 1;
        $value = new Value( array( $contentId ) );
        self::assertSame( array( $contentId ), $value->destinationContentIds );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Value::__toString
     */
    public function testFieldValueToString()
    {
        $contentId = 1;
        $value = new Value( array( $contentId ) );
        self::assertSame( (string)$contentId, (string)$value );
    }
}
