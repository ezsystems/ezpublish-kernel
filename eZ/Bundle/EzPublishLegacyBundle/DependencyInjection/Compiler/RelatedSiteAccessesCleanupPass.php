<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to cleanup related siteaccess, i.e. remove from relation map those in legacy mode.
 */
class RelatedSiteAccessesCleanupPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        $configResolver = $container->get( 'ezpublish.config.resolver.core' );
        $relationMap = $container->getParameter( 'ezpublish.siteaccess.relation_map' );

        // Exclude siteaccesses in legacy_mode (e.g. admin interface)
        foreach ( $relationMap as $repository => &$saByRootLocation )
        {
            foreach ( $saByRootLocation as $rootLocation => $saList )
            {
                foreach ( $saList as $i => $sa )
                {
                    if ( $configResolver->getParameter( 'legacy_mode', 'ezsettings', $sa ) === true )
                    {
                        unset( $saByRootLocation[$rootLocation][$i] );
                    }
                }
            }
        }
        $container->setParameter( 'ezpublish.siteaccess.relation_map', $relationMap );

        $saList = $container->getParameter( 'ezpublish.siteaccess.list' );
        foreach ( $saList as $sa )
        {
            if ( $configResolver->getParameter( 'legacy_mode', 'ezsettings', $sa ) === true )
            {
                continue;
            }

            $relatedSAs = $configResolver->getParameter( 'related_siteaccesses', 'ezsettings', $sa );
            foreach ( $relatedSAs as $i => $relatedSa )
            {
                if ( $configResolver->getParameter( 'legacy_mode', 'ezsettings', $relatedSa ) === true )
                {
                    unset( $relatedSAs[$i] );
                }
            }
            $container->setParameter( "ezsettings.$sa.related_siteaccesses", $relatedSAs );
        }
    }
}
