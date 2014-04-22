<?php
/**
 * File containing the FieldValueConverterRegistryPassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Legacy;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\FieldValueConverterRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FieldValueConverterRegistryPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.persistence.legacy.field_value_converter.registry', new Definition() );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new FieldValueConverterRegistryPass() );
    }

    public function testRegisterConverter()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';
        $callback = '::foobar';

        $def = new Definition();
        $def->setClass( $class );
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array(
                'alias' => $fieldTypeIdentifier,
                'lazy' => true,
                'callback' => $callback
            )
        );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.field_value_converter.registry',
            'register',
            array( $fieldTypeIdentifier, $class . $callback )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterConverterNoAlias()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';
        $callback = '::foobar';

        $def = new Definition();
        $def->setClass( $class );
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array(
                'lazy' => true,
                'callback' => $callback
            )
        );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.field_value_converter.registry',
            'register',
            array( $fieldTypeIdentifier, $class . $callback )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterConverterLazyNoCallback()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';
        $callback = '::foobar';

        $def = new Definition();
        $def->setClass( $class );
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array(
                'alias' => $fieldTypeIdentifier,
                'lazy' => true,
            )
        );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.field_value_converter.registry',
            'register',
            array( $fieldTypeIdentifier, $class . $callback )
        );
    }

    public function testRegisterConverterNoLazy()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';

        $def = new Definition();
        $def->setClass( $class );
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array( 'alias' => $fieldTypeIdentifier )
        );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.field_value_converter.registry',
            'register',
            array( $fieldTypeIdentifier, new Reference( $serviceId ) )
        );
    }
}
