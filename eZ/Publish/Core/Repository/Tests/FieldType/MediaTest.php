<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Media\Type as MediaType,
    eZ\Publish\Core\Repository\FieldType\Media\Value as MediaValue,
    eZ\Publish\Core\Repository\FieldType\Media\Handler as MediaHandler,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,
    SplFileInfo as FileInfo,
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
     * @var \splFileInfo
     */
    protected $mediaFileInfo;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler() );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new FileInfo( $this->mediaPath );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testMediaSupportedValidators()
    {
        $ft = new MediaType( $this->repository );
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
        $ft = new MediaType( $this->repository );
        self::assertSame(
            array( 'mediaType' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group ezmedia
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new MediaType( $this->repository );
        $invalidValue = $ft->getDefaultDefaultValue();
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group ezmedia
     */
    public function testAcceptInvalidValue()
    {
        $ft = new MediaType( $this->repository );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new MediaType( $this->repository );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = $ft->buildValue( $this->mediaPath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__constructor
     */
    public function testBuildFieldValueFromString()
    {
        $ft = new MediaType( $this->repository );
        $value = $ft->buildValue( $this->mediaPath );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Value', $value );
        self::assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\IO\\BinaryFile', $value->file );
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
        $ft = new MediaType( $this->repository );
        $value = $ft->buildValue( $this->mediaPath );
        self::assertSame( $value->file->id, (string)$value );
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
        $ft = new MediaType( $this->repository );
        $value = $ft->buildValue( $this->mediaPath );
        self::assertSame( basename( $value->file->id ), $value->filename );
        self::assertSame( $value->file->contentType, $value->mimeType );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testInvalidVirtualProperty()
    {
        $ft = new MediaType( $this->repository );
        $value = $ft->buildValue( $this->mediaPath );
        $value->nonExistingProperty;
    }
}
