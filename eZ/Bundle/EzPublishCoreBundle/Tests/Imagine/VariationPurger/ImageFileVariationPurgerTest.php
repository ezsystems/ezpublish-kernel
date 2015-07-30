<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\VariationPurger;

use ArrayIterator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger\ImageFileVariationPurger;
use eZ\Publish\Core\IO\Values\BinaryFile;

class ImageFileVariationPurgerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \eZ\Publish\Core\IO\IOServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $ioServiceMock;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $pathGeneratorMock;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger\ImageFileVariationPurger */
    protected $purger;

    public function setUp()
    {
        $this->ioServiceMock = $this->getMock('eZ\Publish\Core\IO\IOServiceInterface');
        $this->pathGeneratorMock = $this->getMock('eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator');
    }

    public function testIteratesOverItems()
    {
        $purger = $this->createPurger(
            array(
                'path/to/1st/image.jpg',
                'path/to/2nd/image.png',
            )
        );

        $this->pathGeneratorMock
            ->expects($this->exactly(4))
            ->method('getVariationPath')
            ->withConsecutive(
                array('path/to/1st/image.jpg', 'large'),
                array('path/to/1st/image.jpg', 'gallery'),
                array('path/to/2nd/image.png', 'large'),
                array('path/to/2nd/image.png', 'gallery')
            );

        $purger->purge(array('large', 'gallery'));
    }

    public function testPurgesExistingItem()
    {
        $purger = $this->createPurger(
            array('path/to/file.png')
        );

        $this->pathGeneratorMock
            ->expects($this->once())
            ->method('getVariationPath')
            ->will($this->returnValue('path/to/file_large.png'));

        $this->ioServiceMock
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $this->ioServiceMock
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->will($this->returnValue(new BinaryFile()));

        $this->ioServiceMock
            ->expects($this->once())
            ->method('deleteBinaryFile')
            ->with($this->isInstanceOf('eZ\Publish\Core\IO\Values\BinaryFile'));

        $purger->purge(array('large'));
    }

    public function testDoesNotPurgeNotExistingItem()
    {
        $purger = $this->createPurger(
            array('path/to/file.png')
        );

        $this->pathGeneratorMock
            ->expects($this->once())
            ->method('getVariationPath')
            ->will($this->returnValue('path/to/file_large.png'));

        $this->ioServiceMock
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false));

        $this->ioServiceMock
            ->expects($this->never())
            ->method('loadBinaryFile');

        $this->ioServiceMock
            ->expects($this->never())
            ->method('deleteBinaryFile');

        $purger->purge(array('large'));
    }

    private function createPurger(array $fileList)
    {
        return new ImageFileVariationPurger(new ArrayIterator($fileList), $this->ioServiceMock, $this->pathGeneratorMock);
    }
}
