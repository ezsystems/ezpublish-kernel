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
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ArgumentValueFactory;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @todo test
 */
class IO extends AbstractParser
{
    /** @var ComplexSettingParser */
    private $complexSettingParser;

    public function __construct( ComplexSettingParser $complexSettingParser )
    {
        $this->complexSettingParser = $complexSettingParser;
    }

    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'io' )
                ->info( 'Binary storage options' )
                ->children()
                    ->scalarNode( 'metadata_handler' )
                        ->info( 'Handler uses to manipulate IO files metadata' )
                        ->example( 'default' )
                    ->end()
                    ->scalarNode( 'binarydata_handler' )
                        ->info( 'Handler uses to manipulate IO files binarydata' )
                        ->example( 'default' )
                    ->end()
                    ->scalarNode( 'url_prefix' )
                        ->info( 'Prefix added to binary files uris. A host can also be added' )
                        ->example( '$var_dir$/$storage_dir$, http://static.example.com/' )
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig( array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
    {
        if ( !isset( $scopeSettings['io'] ) )
        {
            return;
        }

        $settings = $scopeSettings['io'];
        if ( isset( $settings['metadata_handler'] ) )
        {
            $contextualizer->setContextualParameter( 'io.metadata_handler', $currentScope, $settings['metadata_handler'] );
        }
        if ( isset( $settings['binarydata_handler'] ) )
        {
            $contextualizer->setContextualParameter( 'io.binarydata_handler', $currentScope, $settings['binarydata_handler'] );
        }
        if ( isset( $settings['url_prefix'] ) )
        {
            $contextualizer->setContextualParameter( 'io.url_prefix', $currentScope, $settings['url_prefix'] );
        }
    }

    /**
     * Post process configuration to add io_root_dir and io_prefix.
     */
    public function postMap( array $config, ContextualizerInterface $contextualizer )
    {
        $container = $contextualizer->getContainer();
        $configResolver = $container->get( 'ezpublish.config.resolver.core' );
        $configResolver->setContainer( $container );

        // we should only write for default, and for sa/sa groups/global IF they have a declared value
        $scopes = array_merge(
            array( 'default' ),
            $config['siteaccess']['list'],
            array_keys( $config['siteaccess']['groups'] )
        );
        foreach ( $scopes as $sa )
        {
            $this->setIoPrefix( $container, $sa );

            // post process io.url_prefix for complex settings
            $postProcessedValue = $this->postProcessComplexSetting( 'io.url_prefix', $sa, $configResolver );
            if ( is_string( $postProcessedValue ) )
            {
                $contextualizer->setContextualParameter( 'io.url_prefix', $sa, $postProcessedValue );
                // $container->setParameter( "ezsettings.$sa.io.url_prefix", $postProcessedValue );
            }
        }
    }

    private function postProcessComplexSetting( $setting, $sa, ConfigResolver $configResolver )
    {
        if ( !$configResolver->hasParameter( $setting, null, $sa ) )
        {
            return false;
        }

        $settingValue = $configResolver->getParameter( $setting, null, $sa );
        if ( !$this->complexSettingParser->containsDynamicSettings( $settingValue ) )
        {
            return false;
        }

        // we kind of need to process this as well, don't we ?
        if ( $this->complexSettingParser->isDynamicSetting( $settingValue ) )
        {
            $parts = $this->complexSettingParser->parseDynamicSetting( $settingValue );
            return $configResolver->getParameter( $parts['param'], null, $sa );
        }

        $factory = new ArgumentValueFactory( $settingValue );
        foreach ( $this->complexSettingParser->parseComplexSetting( $settingValue ) as $dynamicSetting )
        {
            $parts = $this->complexSettingParser->parseDynamicSetting( $dynamicSetting );
            $factory->setDynamicSetting(
                array( $dynamicSetting ),
                $configResolver->getParameter( $parts['param'], null, $sa )
            );
        }
        return $factory->getArgumentValue();
    }

    /**
     * @param $configResolver
     * @param $sa
     * @param $container
     *
     * @return bool|string
     */
    protected function setIoPrefix( ContainerBuilder $container, $sa )
    {
        $configResolver = $container->get( 'ezpublish.config.resolver.core' );

        $hasVarDir = $container->hasParameter( "ezsettings.$sa.var_dir" );
        $hasStorageDir = $container->hasParameter( "ezsettings.$sa.storage_dir" );

        if ( !$hasVarDir && !$hasStorageDir )
        {
            return false;
        }

        $varDir = $hasVarDir ?
            $container->getParameter( "ezsettings.$sa.var_dir" ) :
            $configResolver->getParameter( 'var_dir', null, $sa );

        $storageDir = $hasStorageDir ?
            $container->getParameter( "ezsettings.$sa.storage_dir" ) :
            $configResolver->getParameter( 'storage_dir', null, $sa );

        $ioPrefix = "$varDir/$storageDir";
        $ioRootDir = "%ezpublish_legacy.root_dir%/$ioPrefix";

        $container->setParameter( "ezsettings.$sa.io_root_dir", $ioRootDir );
        $container->setParameter( "ezsettings.$sa.io_prefix", $ioPrefix );
    }
}
