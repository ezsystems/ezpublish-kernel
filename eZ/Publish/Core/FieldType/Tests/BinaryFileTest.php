<?php
/**
 * File containing the BinaryFileTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\BinaryFile\Type as BinaryFileType,
    eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue,
    eZ\Publish\Core\FieldType\BinaryFile\Handler as BinaryFileHandler,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    SplFileInfo as FileInfo,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezbinaryfile
 */
class BinaryFileTest extends FieldTypeTest
{
    /**
     * Path to test image
     * @var string
     */
    protected $imagePath;

    /**
     * FileInfo object for test image
     * @var \splFileInfo
     */
    protected $imageFileInfo;

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
        $this->imagePath = __DIR__ . '/squirrel-developers.jpg';
        $this->imageFileInfo = new FileInfo( $this->imagePath );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        self::assertSame(
            array(
                "FileSizeValidator" => array(
                    "maxFileSize" => array(
                        "type" => "int",
                        "default" => false
                    )
                )
            ),
            $ft->getValidatorConfigurationSchema(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        self::assertSame(
            array(),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $invalidValue = $ft->getDefaultDefaultValue();
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptInvalidValue()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = $ft->buildValue( $this->imagePath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $this->markTestIncomplete( "Test for \\eZ\\Publish\\Core\\FieldType\\BinaryFile\\Type::toPersistenceValue() is not implemented." );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::fromPersistenceValue
     */
    public function testFromPersistenceValue()
    {
        $this->markTestIncomplete( "Test for \\eZ\\Publish\\Core\\FieldType\\BinaryFile\\Type::fromPersistenceValue() is not implemented." );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Value::__toString
     */
    public function testFieldValueToString()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->imagePath );
        self::assertSame( $value->file->id, (string)$value );
    }

    /**
     * Tests legacy properties, not directly accessible from Value object
     *
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Value::__get
     */
    public function testVirtualLegacyProperty()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->imagePath );
        self::assertSame( basename( $value->file->id ), $value->filename );
        self::assertSame( $value->file->mimeType, $value->mimeType );
        self::assertSame( $value->file->id, $value->filepath );
        self::assertSame( $value->file->size, $value->filesize );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Value::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testInvalidVirtualProperty()
    {
        $ft = new BinaryFileType( $this->validatorService, $this->fieldTypeTools, $this->repository->getIOService() );
        $value = $ft->buildValue( $this->imagePath );
        $value->nonExistingProperty;
    }
}
