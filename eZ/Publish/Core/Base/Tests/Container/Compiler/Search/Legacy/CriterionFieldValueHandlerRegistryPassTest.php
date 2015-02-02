<?php
/**
 * File containing the CriterionFieldValueHandlerRegistryPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Legacy;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\CriterionFieldValueHandlerRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CriterionFieldValueHandlerRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(
            'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler.registry',
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
        $container->addCompilerPass( new CriterionFieldValueHandlerRegistryPass() );
    }

    public function testRegisterValueHandler()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag(
            'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler',
            array( 'alias' => $fieldTypeIdentifier )
        );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler.registry',
            'register',
            array( $fieldTypeIdentifier, new Reference( $serviceId ) )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterValueHandlerNoAlias()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler.registry',
            'register',
            array( $fieldTypeIdentifier, new Reference( $serviceId ) )
        );
    }
}
