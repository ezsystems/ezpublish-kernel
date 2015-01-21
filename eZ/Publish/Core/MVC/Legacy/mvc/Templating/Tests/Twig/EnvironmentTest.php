<?php
/**
 * File containing the TwigEnvironmentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests\Twig;

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment;
use PHPUnit_Framework_TestCase;

class EnvironmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment::loadTemplate
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getTemplateName
     */
    public function testLoadTemplateLegacy()
    {
        $legacyEngine = $this->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\LegacyEngine' )
            ->disableOriginalConstructor()
            ->getMock();

        $templateName = 'design:test/helloworld.tpl';
        $legacyEngine->expects( $this->any() )
            ->method( 'supports' )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $legacyEngine->expects( $this->any() )
            ->method( 'exists' )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $twigEnv = new Environment( $this->getMock( 'Twig_LoaderInterface' ) );
        $twigEnv->setEzLegacyEngine( $legacyEngine );
        $template = $twigEnv->loadTemplate( $templateName );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\Twig\\Template', $template );
        $this->assertSame( $templateName, $template->getTemplateName() );

        // Calling loadTemplate a 2nd time with the same template name should return the very same Template object.
        $this->assertSame( $template, $twigEnv->loadTemplate( $templateName ) );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment::loadTemplate
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getTemplateName
     *
     * @expectedException \Twig_Error_Loader
     */
    public function testLoadNonExistingTemplateLegacy()
    {
        $legacyEngine = $this->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\LegacyEngine' )
            ->disableOriginalConstructor()
            ->getMock();

        $templateName = 'design:test/helloworld.tpl';
        $legacyEngine->expects( $this->any() )
            ->method( 'supports' )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $legacyEngine->expects( $this->any() )
            ->method( 'exists' )
            ->with( $templateName )
            ->will( $this->returnValue( false ) );

        $twigEnv = new Environment( $this->getMock( 'Twig_LoaderInterface' ) );
        $twigEnv->setEzLegacyEngine( $legacyEngine );
        $template = $twigEnv->loadTemplate( $templateName );
    }
}
