<?php
/**
 * File containing the LegacyBundlesPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Iterates over bundles, and uses the extension_locator to store the list of extra legacy extensions in the
 * ezpublish_legacy.legacy_bundles_extensions container parameter.
 */
class LegacyBundlesPass implements CompilerPassInterface
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;

    public function __construct( KernelInterface $kernel )
    {
        $this->kernel = $kernel;
    }

    public function process( ContainerBuilder $container )
    {
        if ( !$container->has( 'ezpublish_legacy.legacy_bundles.extension_locator' ) )
        {
            return;
        }

        $locator = $container->get( 'ezpublish_legacy.legacy_bundles.extension_locator' );

        $extensionNames = array();
        foreach ( $this->kernel->getBundles() as $bundle )
        {
            $extensionNames += array_flip( $locator->getExtensionNames( $bundle ) );
        }

        $container->setParameter( 'ezpublish_legacy.legacy_bundles_extensions', array_keys( $extensionNames ) );
    }
}
