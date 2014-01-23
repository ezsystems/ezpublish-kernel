<?php
/**
 * File containing the TwigBaseTemplateResolverTest class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishDebugBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishDebugBundle\DependencyInjection\TwigBaseTemplateResolver;
use PHPUnit_Framework_TestCase;

class TwigBaseTemplateResolverTest extends PHPUnit_Framework_TestCase
{
    /** @var TwigBaseTemplateResolver */
    protected $resolver;

    const TEMPLATE_CLASS = 'eZ\Bundle\EzPublishCoreBundle\Twig\DebugTemplate';

    public function setUp()
    {
        $this->resolver = new TwigBaseTemplateResolver();
    }

    public function testResolveDebugEnabled()
    {
        $options = $this->resolver->resolve( true, array() );
        self::assertArrayHasKey( 'base_template_class', $options );
        self::assertEquals( self::TEMPLATE_CLASS, $options['base_template_class'] );
    }

    public function testResolveDebugDisabled()
    {
        $options = $this->resolver->resolve( false, array() );
        self::assertArrayNotHasKey( 'base_template_class', $options );
    }

    public function testResolveBaseTemplateAlreadyDefined()
    {
        $options = $this->resolver->resolve( true, array( 'base_template_class' => 'Acme\Dynamite' ) );
        self::assertArrayHasKey( 'base_template_class', $options );
        self::assertEquals( 'Acme\Dynamite', $options['base_template_class'] );
    }
}
