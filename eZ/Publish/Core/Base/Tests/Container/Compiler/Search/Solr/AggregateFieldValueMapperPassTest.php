<?php
/**
 * File containing the AggregateFieldValueMapperPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Solr;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Solr\AggregateFieldValueMapperPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AggregateFieldValueMapperPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(
            'ezpublish.persistence.solr.search.content.field_value_mapper.aggregate',
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
        $container->addCompilerPass( new AggregateFieldValueMapperPass() );
    }

    public function testAddMapper()
    {
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.solr.search.content.field_value_mapper' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.solr.search.content.field_value_mapper.aggregate',
            'addMapper',
            array( new Reference( $serviceId ) )
        );
    }
}
