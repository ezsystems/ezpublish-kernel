<?php
/**
 * File containing the EzPublishElasticsearchBundle class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch\AggregateFacetBuilderVisitorPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch\AggregateFieldValueMapperPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch\AggregateSortClauseVisitorContentPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch\AggregateSortClauseVisitorLocationPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch\CriterionVisitorDispatcherContentPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch\CriterionVisitorDispatcherLocationPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Solr\FieldRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Solr\SignalSlotPass;

class EzPublishElasticsearchBundle extends Bundle
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
    }
}
