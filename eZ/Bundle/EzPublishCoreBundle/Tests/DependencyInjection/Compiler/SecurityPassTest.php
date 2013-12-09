<?php
/**
 * File containing the SecurityPassTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SecurityPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SecurityPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'security.authentication.provider.dao', new Definition() );
        $this->setDefinition( 'security.authentication.provider.anonymous', new Definition() );
    }

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new SecurityPass() );
    }

    public function testAlteredDaoAuthenticationProvider()
    {
        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.authentication.provider.dao',
            'setLazyRepository',
            array( new Reference( 'ezpublish.api.repository.lazy' ) )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.authentication.provider.anonymous',
            'setLazyRepository',
            array( new Reference( 'ezpublish.api.repository.lazy' ) )
        );
    }
}
