<?php
/**
 * File containing the EzPublishSolrBundle class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler\AggregateCriterionVisitorPass;
use eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler\AggregateFacetBuilderVisitorPass;
use eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler\AggregateFieldValueMapperPass;
use eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler\AggregateSortClauseVisitorPass;
use eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler\FieldRegistryPass;
use eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler\SignalSlotPass;

class EzPublishSolrBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new AggregateCriterionVisitorPass );
        $container->addCompilerPass( new AggregateFacetBuilderVisitorPass );
        $container->addCompilerPass( new AggregateFieldValueMapperPass );
        $container->addCompilerPass( new AggregateSortClauseVisitorPass );
        $container->addCompilerPass( new FieldRegistryPass );
        $container->addCompilerPass( new SignalSlotPass );
    }
}
