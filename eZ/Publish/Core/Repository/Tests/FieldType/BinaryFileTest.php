<?php
/**
 * File containing the BinaryFileTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\BinaryFile\Type as BinaryFileType,
    eZ\Publish\Core\Repository\FieldType\BinaryFile\Value as BinaryFileValue,
    eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler as BinaryFileHandler,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,
    SplFileInfo as FileInfo,
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
        $this->repository = new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler() );
        $this->imagePath = __DIR__ . '/squirrel-developers.jpg';
        $this->imageFileInfo = new FileInfo( $this->imagePath );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\FieldType::allowedValidators
     */
    public function testBinaryFileSupportedValidators()
    {
        $ft = new BinaryFileType( $this->repository );
        self::assertSame(
            array( 'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator' ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group binaryFile
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new BinaryFileType( ( $this->repository ) );
        $invalidValue = $ft->getDefaultDefaultValue();
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group binaryFile
     */
    public function testAcceptInvalidValue()
    {
        $ft = new BinaryFileType( $this->repository );
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
        $ft = new BinaryFileType( $this->repository );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = $ft->buildValue( $this->imagePath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }


    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::__toString
     */
    public function testFieldValueToString()
    {
        $ft = new BinaryFileType( $this->repository );
        $value = $ft->buildValue( $this->imagePath );
        self::assertSame( $value->file->id, (string)$value );
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
        $ft = new BinaryFileType( $this->repository );
        $value = $ft->buildValue( $this->imagePath );
        self::assertSame( basename( $value->file->id ), $value->filename );
        self::assertSame( $value->file->contentType, $value->mimeType );
        self::assertSame( $value->file->id, $value->filepath );
        self::assertSame( $value->file->size, $value->filesize );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testInvalidVirtualProperty()
    {
        $ft = new BinaryFileType( $this->repository );
        $value = $ft->buildValue( $this->imagePath );
        $value->nonExistingProperty;
    }
}
