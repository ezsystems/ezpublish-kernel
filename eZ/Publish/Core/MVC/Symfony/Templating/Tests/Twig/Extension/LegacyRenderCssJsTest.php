<?php
/**
 * File containing the LegacyRenderCssJsTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Extension\LegacyExtension;

/**
 * Class LegacyRenderCssJsTest
 *
 * @package eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension
 */
class LegacyRenderCssJsTest extends FileSystemTwigIntegrationTestCase
{
    /**
     * @param array $jsFiles
     */
    protected $jsFiles;

    /**
     * @param array $cssFiles
     */
    protected $cssFiles;

    /**
     * @return array $jsFiles
     */
    public function getJsFiles()
    {
        return $this->jsFiles;
    }

    /**
     * @param array $jsFiles
     */
    public function setJsFiles( array $jsFiles )
    {
        $this->jsFiles = $jsFiles;
    }

    /**
     * @return array $cssFiles
     */
    public function getCssFiles()
    {
        return $this->cssFiles;
    }

    /**
     * @param array $cssFiles
     */
    public function setCssFiles( array $cssFiles )
    {
        $this->cssFiles = $cssFiles;
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $templatesPath = 'templates/';

        return array(
            new LegacyExtension(
                $this->getLegacyEngineMock(),
                $this->getLegacyHelperMock(),
                $templatesPath . 'ez_legacy_render_js.html.twig',
                $templatesPath . 'ez_legacy_render_css.html.twig'
            )
        );
    }

    /**
     * @return string
     */
    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/ez_legacy_render_css_js/';
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLegacyEngineMock()
    {
        $multipleObjectConverterMock = $this->getMock( 'eZ\Publish\Core\MVC\Legacy\Templating\Converter\MultipleObjectConverter' );

        return $this->getMock(
            'eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine',
            array(),
            array(
                function ()
                {
                },
                $multipleObjectConverterMock
            )
        );

    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLegacyHelperMock()
    {
        $legacyHelperMock = $this->getMock(
            'eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper',
            array(),
            array(
                function ()
                {
                }
            )
        );

        $legacyHelperMock->expects( $this->any() )
            ->method( "get" )
            ->with(
                $this->logicalOr(
                    $this->equalTo( "js_files" ),
                    $this->equalTo( "css_files" )
                )
            )
            ->will(
                $this->returnCallback(
                    array( $this, "legacyHelperMockCallback" )
                )
            );

        return $legacyHelperMock;
    }

    /**
     * Callback multiplexer for LegacyHelper::get().
     *
     * @param $id
     *
     * @return mixed
     */
    public function legacyHelperMockCallback( $id )
    {
        switch ( $id )
        {
            case "js_files":
                return $this->getJsFiles();

            case "css_files":
                return $this->getCssFiles();
        }

        return null;
    }
}
