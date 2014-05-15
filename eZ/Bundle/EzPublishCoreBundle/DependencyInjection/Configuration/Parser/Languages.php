<?php
/**
 * File containing the Languages class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Languages extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'languages' )
                ->cannotBeEmpty()
                ->info( 'Available languages, in order of precedence' )
                ->example( array( 'fre-FR', 'eng-GB' ) )
                ->prototype( 'scalar' )->end()
            ->end()
            ->arrayNode( 'translation_siteaccesses' )
                ->info( 'List of "translation siteaccesses" which can be used by language switcher.' )
                ->example( array( 'french_siteaccess', 'english_siteaccess' ) )
                ->prototype( 'scalar' )->end()
            ->end();
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        $this->registerInternalConfigArray( 'languages', $config, $container, self::UNIQUE );
        $this->registerInternalConfigArray( 'translation_siteaccesses', $config, $container, self::UNIQUE );

        $siteAccessesByLanguage = $container->hasParameter( 'ezpublish.siteaccesses_by_language' ) ? $container->getParameter( 'ezpublish.siteaccesses_by_language' ) : array();
        foreach ( $config[$this->baseKey] as $sa => $settings )
        {
            if ( $container->hasParameter( "ezsettings.$sa.languages" ) )
            {
                $languages = $container->getParameter( "ezsettings.$sa.languages" );
                $mainLanguage = array_shift( $languages );
                if ( $mainLanguage )
                {
                    $siteAccessesByLanguage[$mainLanguage][] = $sa;
                }
            }
        }

        $container->setParameter( 'ezpublish.siteaccesses_by_language', $siteAccessesByLanguage );
    }
}
