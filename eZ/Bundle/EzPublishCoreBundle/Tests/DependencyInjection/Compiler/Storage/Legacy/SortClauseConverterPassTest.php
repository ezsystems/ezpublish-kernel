<?php
/**
 * File containing the SortClauseConverterPassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler\Storage\Legacy;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\Storage\Legacy\SortClauseConverterPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SortClauseConverterPassTest extends AbstractCompilerPassTest
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

    public function testAddContentAndCommonHandlers()
    {
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            new Definition()
        );

        $contentServiceId = 'content_service_id';
        $def1 = new Definition();
        $def1->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.content' );
        $this->setDefinition( $contentServiceId, $def1 );

        $commonServiceId = 'common_service_id';
        $def2 = new Definition();
        $def2->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.common' );
        $this->setDefinition( $commonServiceId, $def2 );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            'addHandler',
            array( new Reference( $contentServiceId ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            'addHandler',
            array( new Reference( $commonServiceId ) )
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

    public function testAddLocationAndCommonHandlers()
    {
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            new Definition()
        );

        $locationServiceId = 'content_service_id';
        $def1 = new Definition();
        $def1->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.location' );
        $this->setDefinition( $locationServiceId, $def1 );

        $commonServiceId = 'common_service_id';
        $def2 = new Definition();
        $def2->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.common' );
        $this->setDefinition( $commonServiceId, $def2 );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            'addHandler',
            array( new Reference( $locationServiceId ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            'addHandler',
            array( new Reference( $commonServiceId ) )
        );
    }

    public function testAddLocationAndContentAndCommonHandlers()
    {
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            new Definition()
        );
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            new Definition()
        );

        $contentServiceId = 'content_service_id';
        $def1 = new Definition();
        $def1->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.content' );
        $this->setDefinition( $contentServiceId, $def1 );

        $locationServiceId = 'location_service_id';
        $def2 = new Definition();
        $def2->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.location' );
        $this->setDefinition( $locationServiceId, $def2 );

        $commonServiceId = 'common_service_id';
        $def3 = new Definition();
        $def3->addTag( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.common' );
        $this->setDefinition( $commonServiceId, $def3 );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            'addHandler',
            array( new Reference( $contentServiceId ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content',
            'addHandler',
            array( new Reference( $commonServiceId ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            'addHandler',
            array( new Reference( $locationServiceId ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location',
            'addHandler',
            array( new Reference( $commonServiceId ) )
        );
    }
}
