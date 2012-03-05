<?php
/**
 * File containing the BinaryFileHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler as BinaryFileHandler,
    ezp\Io\SysInfo,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository,
    PHPUnit_Framework_TestCase;

/**
 * Test case for {@link \eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler}
 */
class BinaryFileHandlerTest extends PHPUnit_Framework_TestCase
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

    /**
     * Binary file handler object
     * @var \eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->imagePath = __DIR__ . '/squirrel-developers.jpg';
        $this->imageFileInfo = new FileInfo( $this->imagePath );
        $this->handler = new BinaryFileHandler;
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler::createFromLocalPath
     */
    public function testCreateFromLocalPath()
    {
        $file = $this->handler->createFromLocalPath( $this->imagePath );
        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $file );

        $pathPattern = '#^' . SysInfo::storageDirectory() . '/original/' .
                       $this->imageFileInfo->getContentType()->type .
                       '/[a-z0-9]{32}.' . $this->imageFileInfo->getExtension() . '$#';
        self::assertRegExp( $pathPattern, $file->path );
        self::assertSame( $file->originalFile, $this->imageFileInfo->getBasename() );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler::getBinaryRepository
     */
    public function testGetBinaryRepository()
    {
        self::assertInstanceOf( 'ezp\\Base\\BinaryRepository', $this->handler->getBinaryRepository() );
    }
}
