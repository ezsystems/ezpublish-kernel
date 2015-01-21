<?php
/**
 * File containing the TemplateTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests\Twig;

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template;
use PHPUnit_Framework_TestCase;

class TemplateTest extends PHPUnit_Framework_TestCase
{
    const TEMPLATE_NAME = 'design:hello_world.tpl';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $legacyEngine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $twigEnv;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template
     */
    private $template;

    protected function setUp()
    {
        parent::setUp();
        $this->legacyEngine = $this->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\LegacyEngine' )
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigEnv = $this->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\Twig\\Environment' )
            ->disableOriginalConstructor()
            ->getMock();

        $this->template = new Template( self::TEMPLATE_NAME, $this->twigEnv, $this->legacyEngine );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getEnvironment
     */
    public function testGetEnvironment()
    {
        $this->assertSame( $this->twigEnv, $this->template->getEnvironment() );
    }
    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getTemplateName
     */
    public function testGetName()
    {
        $this->assertSame( self::TEMPLATE_NAME, $this->template->getTemplateName() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::render
     */
    public function testRender()
    {
        $tplParams = array( 'foo' => 'bar', 'truc' => 'muche' );
        $this->legacyEngine
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( self::TEMPLATE_NAME, $tplParams );
        $this->template->render( $tplParams );
    }
}
