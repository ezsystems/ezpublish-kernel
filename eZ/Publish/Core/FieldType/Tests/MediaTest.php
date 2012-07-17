<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Media\Type as MediaType,
    eZ\Publish\Core\FieldType\Media\Value as MediaValue,
    eZ\Publish\Core\FieldType\Media\Handler as MediaHandler,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    SplFileInfo as FileInfo,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezmedia
 */
class MediaTest extends FieldTypeTest
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
        $this->repository = new Repository(
            new InMemoryPersistenceHandler( $this->validatorService, $this->fieldTypeTools ),
            new InMemoryIOHandler()
        );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new FileInfo( $this->mediaPath );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        self::assertSame(
            array(
                "FileSizeValidator" => array(
                    "type" => "int",
                    'maxFileSize' => false
                )
            ),
            $ft->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        self::assertSame(
            array(
                'mediaType' => MediaType::TYPE_HTML5_VIDEO
            ),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Media\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $invalidValue = $ft->getDefaultDefaultValue();
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Media\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptInvalidValue()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Media\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = $ft->buildValue( $this->mediaPath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Media\Value::__construct
     */
    public function testBuildFieldValueFromString()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->mediaPath );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Media\\Value', $value );
        self::assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\IO\\BinaryFile', $value->file );
        self::assertSame( $this->mediaFileInfo->getBasename(), $value->originalFilename );
        self::assertSame( $value->originalFilename, $value->file->originalFile );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Media\Value::__toString
     */
    public function testFieldValueToString()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->mediaPath );
        self::assertSame( $value->file->id, (string)$value );
    }

    /**
     * Tests legacy properties, not directly accessible from Value object
     *
     * @covers \eZ\Publish\Core\FieldType\Media\Value::__get
     */
    public function testVirtualLegacyProperty()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->mediaPath );
        self::assertSame( basename( $value->file->id ), $value->filename );
        self::assertSame( $value->file->mimeType, $value->mimeType );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Media\Value::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testInvalidVirtualProperty()
    {
        $ft = new MediaType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->mediaPath );
        $value->nonExistingProperty;
    }
}
