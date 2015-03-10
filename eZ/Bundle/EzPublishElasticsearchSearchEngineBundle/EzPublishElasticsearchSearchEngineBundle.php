<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchSearchEngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch\AggregateFacetBuilderVisitorPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch\AggregateFieldValueMapperPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch\AggregateSortClauseVisitorContentPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch\AggregateSortClauseVisitorLocationPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch\CriterionVisitorDispatcherContentPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch\CriterionVisitorDispatcherLocationPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\SignalSlotPass;
use eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\DependencyInjection\Compiler;

class EzPublishElasticsearchSearchEngineBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new AggregateFacetBuilderVisitorPass );
        $container->addCompilerPass( new AggregateFieldValueMapperPass );
        $container->addCompilerPass( new AggregateSortClauseVisitorContentPass );
        $container->addCompilerPass( new AggregateSortClauseVisitorLocationPass );
        $container->addCompilerPass( new CriterionVisitorDispatcherContentPass );
        $container->addCompilerPass( new CriterionVisitorDispatcherLocationPass );

        // @todo two passes below should be common for search implementations, so maybe separate or Core bundle
        $container->addCompilerPass( new FieldRegistryPass );
        $container->addCompilerPass( new SignalSlotPass );

        $connectionParameterFactoryId = "ezpublish.elasticsearch.connection_parameter_factory";
        $container->addCompilerPass( new Compiler\ContentGatewayPass( $connectionParameterFactoryId ) );
        $container->addCompilerPass( new Compiler\ContentHandlerPass( $connectionParameterFactoryId ) );
        $container->addCompilerPass( new Compiler\HttpClientPass( $connectionParameterFactoryId ) );
        $container->addCompilerPass( new Compiler\LocationGatewayPass( $connectionParameterFactoryId ) );
        $container->addCompilerPass( new Compiler\LocationHandlerPass( $connectionParameterFactoryId ) );
    }

    public function getContainerExtension()
    {
        if ( !isset( $this->extension ) )
        {
            $this->extension = new DependencyInjection\EzPublishElasticsearchSearchEngineExtension();
        }

        return $this->extension;
    }
}
