<?php
/**
 * File containing the Kernel class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Legacy\Tests;

use Exception;
use eZ\Publish\Core\MVC\Legacy\Kernel;
use ezpKernelHandler;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class KernelTest extends PHPUnit_Framework_TestCase
{
    /** @var ezpKernelHandler|PHPUnit_Framework_MockObject_MockObject */
    protected $kernelHandlerMock;

    /** @var Kernel */
    protected $legacyKernel;

    public function testRunCallbackWithException()
    {
        $this->getKernelHandlerMock()
            ->expects( $this->any() )
            ->method( 'runCallback' )
            ->will( $this->throwException( new Exception ) );

        $iterations = 1;
        do
        {
            try
            {
                $this->getLegacyKernel()->runCallback( '' );
            }
            // this will occur on the 2nd iteration if the kernel state hasn't been correctly reset
            catch ( RuntimeException $e )
            {
                $this->fail( "LegacyKernel was not reinitialized after the first exception" );
            }
            catch ( Exception $e )
            {
            }
        }
        while ( $iterations++ < 2 );
    }

    /**
     * @return Kernel
     */
    protected function getLegacyKernel()
    {
        if ( !isset( $this->legacyKernel ) )
        {
            $this->legacyKernel = new Kernel( $this->getKernelHandlerMock(), '/tmp', '/tmp' );
        }
        return $this->legacyKernel;
    }

    /**
     * @return ezpKernelHandler|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getKernelHandlerMock()
    {
        if ( !isset( $this->kernelHandlerMock ) )
        {
            $this->kernelHandlerMock = $this->getMock( 'ezpKernelHandler' );
        }
        return $this->kernelHandlerMock;
    }
}
