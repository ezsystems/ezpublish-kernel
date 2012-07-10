<?php
/**
 * File containing the BinaryFileHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\BinaryFile\Handler as BinaryFileHandler,
    splFileInfo,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,
    PHPUnit_Framework_TestCase;

/**
 * Test case for {@link \eZ\Publish\Core\FieldType\BinaryFile\Handler}
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
     * @var \splFileInfo
     */
    protected $imageFileInfo;

    /**
     * Binary file handler object
     * @var \eZ\Publish\Core\FieldType\BinaryFile\Handler
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        $repository = new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler() );
        $this->imagePath = __DIR__ . '/squirrel-developers.jpg';
        $this->imageFileInfo = new splFileInfo( $this->imagePath );
        $this->handler = new BinaryFileHandler( $repository->getIOService() );
    }

    /**
     * @group fieldType
     * @group binaryFile
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Handler::createFromLocalPath
     */
    public function testCreateFromLocalPath()
    {
        $file = $this->handler->createFromLocalPath( $this->imagePath );
        self::assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\IO\\BinaryFile', $file );
    }
}
