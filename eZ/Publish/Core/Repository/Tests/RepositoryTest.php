<?php
/**
 * File containing the RepositoryTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\Core\Repository\Repository;

/**
 * @group repository
 */
class RepositoryTest extends BaseServiceTest
{
    /**
     * Generate \eZ\Publish\Core\Repository\Repository
     *
     * Makes it possible to inject different Io / Persistence handlers
     *
     * @param array $serviceSettings Array with settings that are passed to Services
     * @return \eZ\Publish\Core\Repository\Repository
     */
    protected function getRepository( array $serviceSettings )
    {
        $serviceSettings['legacy'] = array(
            'legacy_root_dir'   => getcwd()
        );

        return new Repository(
            $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Handler' ),
            $this->getMock( 'eZ\\Publish\\SPI\\IO\\Handler' ),
            $serviceSettings
        );
    }

    /**
     * Returns a legacy kernel mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLegacyKernelMock()
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\Legacy\\Kernel' )
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @covers eZ\Publish\Core\Repository\Repository::getLegacyKernel
     */
    public function testGetLegacyKernel()
    {
        $legacyKernel = $this->repository->getLegacyKernel();
        self::assertInstanceOf( 'eZ\\Publish\\Legacy\\Kernel', $legacyKernel );
        // Now checks that legacy kernel is built only once
        self::assertSame( $legacyKernel, $this->repository->getLegacyKernel() );
    }

    /**
     * @covers eZ\Publish\Core\Repository\Repository::getLegacyKernel
     * @expectedException eZ\Publish\Core\Base\Exceptions\BadConfiguration
     */
    public function testGetLegacyKernelFail()
    {
        $refRepository = new \ReflectionObject( $this->repository );
        $refServiceSettings = $refRepository->getProperty( 'serviceSettings' );
        $refServiceSettings->setAccessible( true );
        $serviceSettings = $refServiceSettings->getValue( $this->repository );
        unset( $serviceSettings['legacy']['legacy_root_dir'] );
        $refServiceSettings->setValue( $this->repository, $serviceSettings );

        $this->repository->getLegacyKernel();
    }

    /**
     * @covers eZ\Publish\Core\Repository\Repository::setLegacyKernel
     * @covers eZ\Publish\Core\Repository\Repository::getLegacyKernel
     */
    public function testSetLegacyKernel()
    {
        $legacyKernelMock = $this->getLegacyKernelMock();
        $this->repository->setLegacyKernel( $legacyKernelMock );
        self::assertSame( $legacyKernelMock, $this->repository->getLegacyKernel() );
    }
}
