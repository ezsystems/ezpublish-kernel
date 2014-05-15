<?php
/**
 * File containing the RoleLimitationConverterPassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Legacy;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\RoleLimitationConverterPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RoleLimitationConverterPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(
            'ezpublish.persistence.legacy.role.limitation.converter',
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
        $container->addCompilerPass( new RoleLimitationConverterPass() );
    }

    public function testRegisterRoleLimitationConverter()
    {
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.legacy.role.limitation.handler' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.role.limitation.converter',
            'addHandler',
            array( new Reference( $serviceId ) )
        );
    }
}
