<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\Media\Type as MediaType,
    eZ\Publish\Core\Repository\FieldType\Media\Value as MediaValue,
    eZ\Publish\Core\Repository\FieldType\Media\Handler as MediaHandler,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class MediaTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to test media
     * @var string
     */
    protected $mediaPath;

    /**
     * FileInfo object for test image
     * @var \ezp\Io\FileInfo
     */
    protected $mediaFileInfo;

    protected function setUp()
    {
        parent::setUp();
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new FileInfo( $this->mediaPath );
    }

    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Type",
            Factory::build( "ezmedia" ),
            "BinaryFile object not returned for 'ezmedia', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testMediaSupportedValidators()
    {
        $ft = new MediaType;
        self::assertSame(
            array( 'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator' ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
     */
    public function testMediaAllowedSettings()
    {
        $ft = new MediaType;
        self::assertSame(
            array( 'mediaType' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     * @group ezmedia
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new MediaType;
        $invalidValue = new MediaValue;
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::canParseValue
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @group fieldType
     * @group ezmedia
     */
    public function testCanParseInvalidValue()
    {
        $ft = new MediaType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new MediaType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );

        $handler = new MediaHandler;
        $value = new MediaValue;
        $value->file = $handler->createFromLocalPath( $this->mediaPath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::getHandler
     */
    public function testValueGetHandler()
    {
        $value = new MediaValue;
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Handler', $value->getHandler() );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Value', $value );
        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $value->file );
        self::assertSame( $this->mediaFileInfo->getBasename(), $value->originalFilename );
        self::assertSame( $value->originalFilename, $value->file->originalFile );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__toString
     */
    public function testFieldValueToString()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        self::assertSame( $value->file->path, (string)$value );
    }

    /**
     * Tests legacy properties, not directly accessible from Value object
     *
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__get
     */
    public function testVirtualLegacyProperty()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        self::assertSame( basename( $value->file->path ), $value->filename );
        self::assertSame( $value->file->contentType->__toString(), $value->mimeType );
        self::assertSame( $value->file->contentType->type, $value->mimeTypeCategory );
        self::assertSame( $value->file->contentType->subType, $value->mimeTypePart );
        self::assertSame( $value->file->path, $value->filepath );
        self::assertSame( $value->file->size, $value->filesize );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testInvalidVirtualProperty()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        $value->nonExistingProperty;
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::onFieldSetValue
     */
    public function testOnFieldSetValue()
    {
        $defaultValue = new MediaValue;
        $value = MediaValue::fromString( $this->mediaPath );
        $ft = new MediaType;
        $ft->setFieldSetting( 'mediaType', MediaType::TYPE_QUICKTIME );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'onFieldSetValue' );
        $refMethod->setAccessible( true );

        $fieldDefMock = $this->getMockBuilder( 'ezp\\Content\\Type\\FieldDefinition' )
            ->setConstructorArgs(
                array(
                    $this->getMock( 'ezp\\Content\\Type' ),
                    'ezmedia'
                )
            )
            ->getMock();
        $fieldDefMock
            ->expects( $this->once() )
            ->method( 'getType' )
            ->will( $this->returnValue( $ft ) );

        $fieldMock = $this->getMockBuilder( 'ezp\\Content\\Field' )
            ->setConstructorArgs(
                array(
                    $this->getMockBuilder( 'ezp\\Content\\Version' )
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $fieldDefMock
                )
            )
            ->getMock();

        $refMethod->invoke( $ft, $fieldMock, $value );
        self::assertSame( $ft->getValue(), $value );
        self::assertSame( MediaHandler::PLUGINSPAGE_QUICKTIME, $ft->getValue()->pluginspage );
    }
}
