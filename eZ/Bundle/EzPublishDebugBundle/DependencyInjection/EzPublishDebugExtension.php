<?php
/**
 * File containing the EzPublishDebugExtension class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDebugBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;

class EzPublishDebugExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        $twigBaseTemplateResolver = new TwigBaseTemplateResolver();
        $container->setParameter(
            'twig.options',
            $twigBaseTemplateResolver->resolve(
                $container->getParameter( 'kernel.debug' ),
                $container->getParameter( 'twig.options' )
            )
        );

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../Resources/config' )
        );

        $configuration = $this->getConfiguration( $configs, $container );

        // Base services and services overrides
        $loader->load( 'services.yml' );
    }
}
