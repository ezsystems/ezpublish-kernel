<?php
/**
 * File containing the AddFieldTypePassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\LegacyBundlesPass;
use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyExtensionsLocatorInterface;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class AddFieldTypePassTest extends AbstractCompilerPassTest
{
    protected $kernelMock;

    protected $locatorMock;

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new LegacyBundlesPass( $this->getKernelMock() ) );
    }

    public function testCompilerPass()
    {
        $bundle1 = $this->createBundleMock( 'Bundle1' );
        $bundle2 = $this->createBundleMock( 'Bundle2' );
        $this->getKernelMock()
            ->expects( $this->any() )
            ->method( 'getBundles' )
            ->will( $this->returnValue( array( $bundle1, $bundle2 ) ) );

        $this->getLocatorMock()
            ->expects( $this->any() )
            ->method( 'getExtensionNames' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $bundle1, array( 'legacy_extension_a', 'legacy_extension_b' ) ),
                        array( $bundle2, array( 'legacy_extension_b', 'legacy_extension_c' ) )
                    )
                )
            );
        $this->container->set(
            'ezpublish_legacy.legacy_bundles.extension_locator',
            $this->getLocatorMock()
        );
        $this->compile();

        $this->assertContainerBuilderHasParameter(
            'ezpublish_legacy.legacy_bundles_extensions',
            array( 'legacy_extension_a', 'legacy_extension_b', 'legacy_extension_c' )
        );
    }

    /**
     * @return KernelInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getKernelMock()
    {
        if ( !isset( $this->kernelMock ) )
        {
            $this->kernelMock = $this->getMock( 'Symfony\Component\HttpKernel\KernelInterface' );
        }
        return $this->kernelMock;
    }

    /**
     * @return BundleInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function createBundleMock( $name )
    {
        $mock = $this->getMock( 'Symfony\Component\HttpKernel\Bundle\BundleInterface' );
        $mock
            ->expects( $this->any() )
            ->method( 'getName' )
            ->will( $this->returnValue( $name ) );
        return $mock;
    }

    /**
     * @return LegacyExtensionsLocatorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLocatorMock()
    {
        if ( !isset( $this->locatorMock ) )
        {
            $this->locatorMock = $this->getMock( 'eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyExtensionsLocatorInterface' );
        }
        return $this->locatorMock;
    }

}
