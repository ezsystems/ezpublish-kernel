<?php
/**
 * File containing the RegisterLimitationTypePassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FieldTypeParameterProviderRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FieldTypeParameterProviderRegistryPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.fieldType.parameterProviderRegistry', new Definition() );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new FieldTypeParameterProviderRegistryPass() );
    }

    public function testRegisterLimitationType()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.fieldType.parameterProvider', array( 'alias' => $fieldTypeIdentifier ) );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.parameterProviderRegistry',
            'setParameterProvider',
            array( new Reference( $serviceId ), $fieldTypeIdentifier )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterLimitationTypeNoAlias()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.fieldType.parameterProvider' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.parameterProviderRegistry',
            'setParameterProvider',
            array( new Reference( $serviceId ), $fieldTypeIdentifier )
        );
    }
}
