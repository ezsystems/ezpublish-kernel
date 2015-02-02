<?php
/**
 * File containing the SortClauseConverterPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Legacy;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\SortClauseConverterPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SortClauseConverterPassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new SortClauseConverterPass() );
    }

    public function testAddContentHandlers()
    {
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            new Definition()
        );

        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.content' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            'addHandler',
            array( new Reference( $serviceId ) )
        );
    }

    public function testAddLocationHandlers()
    {
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            new Definition()
        );

        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.location' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            'addHandler',
            array( new Reference( $serviceId ) )
        );
    }

    public function testAddLocationAndContentHandlers()
    {
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            new Definition()
        );
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            new Definition()
        );

        $commonServiceId = 'common_service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.content' );
        $def->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.location' );
        $this->setDefinition( $commonServiceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            'addHandler',
            array( new Reference( $commonServiceId ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            'addHandler',
            array( new Reference( $commonServiceId ) )
        );
    }
}
