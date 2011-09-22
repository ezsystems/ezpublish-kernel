<?php
/**
 * File containing the BinaryFileTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\BinaryFile\Type as BinaryFileType,
    ezp\Content\FieldType\BinaryFile\Value as BinaryFileValue,
    ezp\Content\FieldType\BinaryFile\Handler as BinaryFileHandler,
    ezp\Io\BinaryFile,
    ezp\Io\FileInfo,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\BinaryRepository,
    ezp\Persistence\Content\FieldValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class BinaryFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to test image
     * @var string
     */
    protected $imagePath;

    /**
     * FileInfo object for test image
     * @var \ezp\Io\FileInfo
     */
    protected $imageFileInfo;

    protected function setUp()
    {
        parent::setUp();
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->imagePath = __DIR__ . '/squirrel-developers.jpg';
        $this->imageFileInfo = new FileInfo( $this->imagePath );
    }


    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @group binaryFile
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\BinaryFile\\Type",
            Factory::build( "ezbinaryfile" ),
            "BinaryFile object not returned for 'ezbinaryfile', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testBinaryFileSupportedValidators()
    {
        $ft = new BinaryFileType;
        self::assertSame(
            array( 'ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator' ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \ezp\Content\FieldType\BinaryFile\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     * @group binaryFile
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new BinaryFileType;
        $invalidValue = new BinaryFileValue;
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \ezp\Content\FieldType\BinaryFile\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new BinaryFileType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );

        $handler = new BinaryFileHandler;
        $value = new BinaryFileValue;
        $value->file = $handler->createFromLocalPath( $this->imagePath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \ezp\Content\FieldType\BinaryFile\Value::getHandler
     */
    public function testValueGetHandler()
    {
        $value = new BinaryFileValue;
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\BinaryFile\\Handler', $value->getHandler() );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \ezp\Content\FieldType\BinaryFile\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $value = BinaryFileValue::fromString( $this->imagePath );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\BinaryFile\\Value', $value );
        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $value->file );
        self::assertSame( $this->imageFileInfo->getBasename(), $value->originalFilename );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \ezp\Content\FieldType\BinaryFile\Value::__toString
     */
    public function testFieldValueToString()
    {
        $value = BinaryFileValue::fromString( $this->imagePath );
        self::assertSame( $value->file->path, (string)$value );
    }
}
