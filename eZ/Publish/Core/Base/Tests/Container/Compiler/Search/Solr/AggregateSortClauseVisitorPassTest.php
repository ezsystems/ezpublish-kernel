<?php
/**
 * File containing the AggregateSortClauseVisitorPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Solr;

use eZ\Publish\Core\Base\Container\Compiler\Search\Solr\AggregateSortClauseVisitorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AggregateSortClauseVisitorPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(
            'ezpublish.search.solr.content.sort_clause_visitor.aggregate',
            new Definition()
        );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new AggregateSortClauseVisitorPass() );
    }

    public function testAddVisitor()
    {
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.search.solr.content.sort_clause_visitor' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.search.solr.content.sort_clause_visitor.aggregate',
            'addVisitor',
            array( new Reference( $serviceId ) )
        );
    }
}
