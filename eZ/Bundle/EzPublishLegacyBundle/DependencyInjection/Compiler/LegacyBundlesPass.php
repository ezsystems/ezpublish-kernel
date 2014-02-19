<?php
/**
 * File containing the LegacyBundlesPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
