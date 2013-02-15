<?php
/**
 * File containing the TwigEnvironmentTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests\Twig;

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment:loadTemplate
     * @covers eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getTemplateName
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

        $twigEnv = new Environment( $this->getMock( 'Twig_LoaderInterface' ) );
        $twigEnv->setEzLegacyEngine( $legacyEngine );
        $template = $twigEnv->loadTemplate( $templateName );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\Twig\\Template', $template );
        $this->assertSame( $templateName, $template->getTemplateName() );

        // Calling loadTemplate a 2nd time with the same template name should return the very same Template object.
        $this->assertSame( $template, $twigEnv->loadTemplate( $templateName ) );
    }
}
