<?php
/**
 * File containing the FieldTypeRegistryPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Solr;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Solr\FieldRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FieldTypeRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.persistence.solr.search.field_registry', new Definition() );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new FieldRegistryPass() );
    }

    public function testRegisterFieldType()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.fieldType.indexable', array( 'alias' => $fieldTypeIdentifier ) );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.solr.search.field_registry',
            'registerType',
            array( $fieldTypeIdentifier, new Reference( $serviceId ) )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterFieldTypeNoAlias()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.fieldType.indexable' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.solr.search.field_registry',
            'registerType',
            array( $fieldTypeIdentifier, new Reference( $serviceId ) )
        );
    }
}
