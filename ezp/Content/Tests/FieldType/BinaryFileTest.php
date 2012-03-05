<?php
/**
 * File containing the BinaryFileTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\BinaryFile\Type as BinaryFileType,
    eZ\Publish\Core\Repository\FieldType\BinaryFile\Value as BinaryFileValue,
    eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler as BinaryFileHandler,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository,
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\Type",
            Factory::build( "ezbinaryfile" ),
            "BinaryFile object not returned for 'ezbinaryfile', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testBinaryFileSupportedValidators()
    {
        $ft = new BinaryFileType;
        self::assertSame(
            array( 'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator' ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @group fieldType
     * @group binaryFile
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new BinaryFileType;
        $invalidValue = new BinaryFileValue;
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @group fieldType
     * @group binaryFile
     */
    public function testAcceptInvalidValue()
    {
        $ft = new BinaryFileType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new BinaryFileType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $handler = new BinaryFileHandler;
        $value = new BinaryFileValue;
        $value->file = $handler->createFromLocalPath( $this->imagePath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::getHandler
     */
    public function testValueGetHandler()
    {
        $value = new BinaryFileValue;
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\Handler', $value->getHandler() );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $value = BinaryFileValue::fromString( $this->imagePath );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\Value', $value );
        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $value->file );
        self::assertSame( $this->imageFileInfo->getBasename(), $value->originalFilename );
        self::assertSame( $value->originalFilename, $value->file->originalFile );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::__toString
     */
    public function testFieldValueToString()
    {
        $value = BinaryFileValue::fromString( $this->imagePath );
        self::assertSame( $value->file->path, (string)$value );
    }

    /**
     * Tests legacy properties, not directly accessible from Value object
     *
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::__get
     */
    public function testVirtualLegacyProperty()
    {
        $value = BinaryFileValue::fromString( $this->imagePath );
        self::assertSame( basename( $value->file->path ), $value->filename );
        self::assertSame( $value->file->contentType->__toString(), $value->mimeType );
        self::assertSame( $value->file->contentType->type, $value->mimeTypeCategory );
        self::assertSame( $value->file->contentType->subType, $value->mimeTypePart );
        self::assertSame( $value->file->path, $value->filepath );
        self::assertSame( $value->file->size, $value->filesize );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testInvalidVirtualProperty()
    {
        $value = BinaryFileValue::fromString( $this->imagePath );
        $value->nonExistingProperty;
    }
}
