<?php
/**
 * File containing the BinaryLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\BinaryLoader;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Values\BinaryFile;
use Liip\ImagineBundle\Model\Binary;
use PHPUnit_Framework_TestCase;

class BinaryLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ioService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionGuesser;

    /**
     * @var BinaryLoader
     */
    private $binaryLoader;

    protected function setUp()
    {
        parent::setUp();
        $this->ioService = $this->getMock( 'eZ\Publish\Core\IO\IOServiceInterface' );
        $this->extensionGuesser = $this->getMock( 'Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface' );
        $this->binaryLoader = new BinaryLoader( $this->ioService, $this->extensionGuesser );
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     */
    public function testFindNotFound()
    {
        $path = 'something.jpg';
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $path )
            ->will( $this->throwException( new NotFoundException( 'foo', 'bar' ) ) );

        $this->binaryLoader->find( $path );
    }

    public function testFind()
    {
        $path = 'something.jpg';
        $mimeType = 'foo/mime-type';
        $content = 'some content';
        $binaryFile = new BinaryFile( array( 'id' => $path, 'mimeType' => $mimeType ) );
        $this->ioService
            ->expects( $this->once() )
            ->method( 'loadBinaryFile' )
            ->with( $path )
            ->will( $this->returnValue( $binaryFile ) );

        $format = 'jpg';
        $this->extensionGuesser
            ->expects( $this->once() )
            ->method( 'guess' )
            ->with( $mimeType )
            ->will( $this->returnValue( $format ) );

        $this->ioService
            ->expects( $this->once() )
            ->method( 'getFileContents' )
            ->with( $binaryFile )
            ->will( $this->returnValue( $content ) );

        $expected = new Binary( $content, $mimeType, $format );
        $this->assertEquals( $expected, $this->binaryLoader->find( $path ) );
    }
}
