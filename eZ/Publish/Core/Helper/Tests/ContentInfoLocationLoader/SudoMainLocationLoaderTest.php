<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\Tests\ContentInfoLocationLoader;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\ContentInfoLocationLoader\SudoMainLocationLoader;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit_Framework_TestCase;

class SudoMainLocationLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Helper\ContentInfoLocationLoader\SudoMainLocationLoader
     */
    private $loader;

    public function setUp()
    {
        $this->loader = new SudoMainLocationLoader($this->getRepositoryMock());
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationNoMainLocation()
    {
        $contentInfo = new ContentInfo();

        $this->getLocationServiceMock()
            ->expects($this->never())
            ->method('loadLocation');

        $this->loader->loadLocation($contentInfo);
    }

    public function testLoadLocation()
    {
        $contentInfo = new ContentInfo(['mainLocationId' => 42]);
        $location = new Location();

        $this->getRepositoryMock()
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));

        $this->getRepositoryMock()
            ->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($this->getLocationServiceMock()));

        $this->getLocationServiceMock()
            ->expects($this->once())
            ->method('loadLocation')
            ->with(42)
            ->will($this->returnValue($location));

        $this->assertSame($location, $this->loader->loadLocation($contentInfo));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationError()
    {
        $contentInfo = new ContentInfo(['mainLocationId' => 42]);
        $location = new Location();

        $this->getRepositoryMock()
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));

        $this->getRepositoryMock()
            ->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($this->getLocationServiceMock()));

        $this->getLocationServiceMock()
            ->expects($this->once())
            ->method('loadLocation')
            ->with(42)
            ->will(
                $this->throwException(new NotFoundException('main location of content', 42))
            );

        $this->assertSame($location, $this->loader->loadLocation($contentInfo));
    }

    /**
     * @return \eZ\Publish\Core\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepositoryMock()
    {
        static $repositoryMock;

        if ($repositoryMock === null) {
            $repositoryClass = 'eZ\Publish\Core\Repository\Repository';

            $repositoryMock = $this
                ->getMockBuilder($repositoryClass)
                ->disableOriginalConstructor()
                ->setMethods(
                    array_diff(
                        get_class_methods($repositoryClass),
                        array('sudo')
                    )
                )
                ->getMock();
        }

        return $repositoryMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\LocationService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLocationServiceMock()
    {
        static $mock;

        if ($mock === null) {
            $mock = $this
                ->getMockBuilder('eZ\Publish\API\Repository\LocationService')
                ->getMock();
        }

        return $mock;
    }

    /**
     * @return \eZ\Publish\Core\Repository\Permission\PermissionResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder('eZ\Publish\Core\Repository\Permission\PermissionResolver')
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\RoleDomainMapper')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\LimitationService')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\SPI\Persistence\User\Handler')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\API\Repository\Values\User\UserReference')
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
