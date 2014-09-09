<?php
/**
 * File containing the RendererTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\RichText;

use eZ\Publish\Core\MVC\Symfony\FieldType\RichText\Renderer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Exception;
use PHPUnit_Framework_TestCase;

class RendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->repositoryMock = $this->getRepositoryMock();
        $this->securityContextMock = $this->getSecurityContextMock();
        $this->configResolverMock = $this->getConfigResolverMock();
        $this->templateEngineMock = $this->getTemplateEngineMock();
        $this->loggerMock = $this->getLoggerMock();
        parent::setUp();
    }

    public function testRenderTag()
    {
        $renderer = $this->getMockedRenderer( array( "render", "getTagTemplateName" ) );
        $name = "tag";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $result = "result";

        $renderer
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $templateName, $parameters )
            ->will( $this->returnValue( $result ) );

        $renderer
            ->expects( $this->once() )
            ->method( "getTagTemplateName" )
            ->with( $name, $isInline )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $this->loggerMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $this->assertEquals(
            $result,
            $renderer->renderTag( $name, $parameters, $isInline )
        );
    }

    public function testRenderTagNoTemplateConfigured()
    {
        $renderer = $this->getMockedRenderer( array( "render", "getTagTemplateName" ) );
        $name = "tag";
        $parameters = array( "parameters" );
        $isInline = true;

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->once() )
            ->method( "getTagTemplateName" )
            ->with( $name, $isInline )
            ->will( $this->returnValue( null ) );

        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( "exists" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render template tag '{$name}': no template configured" );

        $this->assertEquals(
            null,
            $renderer->renderTag( $name, $parameters, $isInline )
        );
    }

    public function testRenderTagNoTemplateFound()
    {
        $renderer = $this->getMockedRenderer( array( "render", "getTagTemplateName" ) );
        $name = "tag";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->once() )
            ->method( "getTagTemplateName" )
            ->with( $name, $isInline )
            ->will( $this->returnValue( "templateName" ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( false ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render template tag '{$name}': template '{$templateName}' does not exists" );

        $this->assertEquals(
            null,
            $renderer->renderTag( $name, $parameters, $isInline )
        );
    }

    public function providerForTestRenderTagWithTemplate()
    {
        return array(
            array(
                $tagName = "tag1",
                array(
                    array( "hasParameter", $namespace = "test.name.space.tag.{$tagName}", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName1" ) ),
                ),
                array(),
                $templateName,
                $templateName,
                false,
                "result",
            ),
            array(
                $tagName = "tag2",
                array(
                    array( "hasParameter", $namespace = "test.name.space.tag.{$tagName}", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName2" ) ),
                ),
                array(),
                $templateName,
                $templateName,
                true,
                "result",
            ),
            array(
                $tagName = "tag3",
                array(
                    array( "hasParameter", "test.name.space.tag.{$tagName}", false ),
                    array( "hasParameter", $namespace = "test.name.space.tag.default", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName3" ) ),
                ),
                array(
                    array( "warning", "Template tag '{$tagName}' configuration was not found" ),
                ),
                $templateName,
                $templateName,
                false,
                "result",
            ),
            array(
                $tagName = "tag4",
                array(
                    array( "hasParameter", "test.name.space.tag.{$tagName}", false ),
                    array( "hasParameter", $namespace = "test.name.space.tag.default_inline", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName4" ) ),
                ),
                array(
                    array( "warning", "Template tag '{$tagName}' configuration was not found" ),
                ),
                $templateName,
                $templateName,
                true,
                "result",
            ),
            array(
                $tagName = "tag5",
                array(
                    array( "hasParameter", "test.name.space.tag.{$tagName}", false ),
                    array( "hasParameter", $namespace = "test.name.space.tag.default", false ),
                ),
                array(
                    array( "warning", "Template tag '{$tagName}' configuration was not found" ),
                    array( "warning", "Template tag '{$tagName}' default configuration was not found" ),
                    array( "error", "Could not render template tag '{$tagName}': no template configured" ),
                ),
                null,
                null,
                false,
                null,
            ),
            array(
                $tagName = "tag6",
                array(
                    array( "hasParameter", "test.name.space.tag.{$tagName}", false ),
                    array( "hasParameter", $namespace = "test.name.space.tag.default_inline", false ),
                ),
                array(
                    array( "warning", "Template tag '{$tagName}' configuration was not found" ),
                    array( "warning", "Template tag '{$tagName}' default configuration was not found" ),
                    array( "error", "Could not render template tag '{$tagName}': no template configured" ),
                ),
                null,
                null,
                true,
                null,
            ),
        );
    }

    /**
     * @dataProvider providerForTestRenderTagWithTemplate
     */
    public function testRenderTagWithTemplate(
        $tagName,
        array $configResolverParams,
        array $loggerParams,
        $templateEngineTemplate,
        $renderTemplate,
        $isInline,
        $renderResult
    )
    {
        $renderer = $this->getMockedRenderer( array( "render" ) );
        $parameters = array( "parameters" );

        if ( !isset( $renderTemplate ) )
        {
            $renderer
                ->expects( $this->never() )
                ->method( "render" );
        }
        else
        {
            $renderer
                ->expects( $this->once() )
                ->method( "render" )
                ->with( $renderTemplate, $parameters )
                ->will( $this->returnValue( $renderResult ) );
        }

        if ( !isset( $templateEngineTemplate ) )
        {
            $this->templateEngineMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $this->templateEngineMock
                ->expects( $this->once() )
                ->method( "exists" )
                ->with( $templateEngineTemplate )
                ->will( $this->returnValue( true ) );
        }

        if ( empty( $configResolverParams ) )
        {
            $this->configResolverMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $i = 0;
            foreach ( $configResolverParams as $params )
            {
                $method = $params[0];
                $namespace = $params[1];
                $returnValue = $params[2];
                $this->configResolverMock
                    ->expects( $this->at( $i ) )
                    ->method( $method )
                    ->with( $namespace )
                    ->will( $this->returnValue( $returnValue ) );
                $i++;
            }
        }

        if ( empty( $loggerParams ) )
        {
            $this->loggerMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $i = 0;
            foreach ( $loggerParams as $params )
            {
                $method = $params[0];
                $message = $params[1];
                $this->loggerMock
                    ->expects( $this->at( $i ) )
                    ->method( $method )
                    ->with( $message );
                $i++;
            }
        }

        $this->assertEquals(
            $renderResult,
            $renderer->renderTag( $tagName, $parameters, $isInline )
        );
    }

    public function testRenderContentEmbed()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkContent", "getEmbedTemplateName" ) );
        $contentId = 42;
        $viewType = "embedTest";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = false;
        $result = "result";

        $renderer
            ->expects( $this->once() )
            ->method( "checkContent" )
            ->with( $contentId );

        $renderer
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $templateName, $parameters )
            ->will( $this->returnValue( $result ) );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $this->loggerMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $this->assertEquals(
            $result,
            $renderer->renderContentEmbed( $contentId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderContentEmbedNoTemplateConfigured()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkContent", "getEmbedTemplateName" ) );
        $contentId = 42;
        $viewType = "embedTest";
        $templateName = null;
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = false;

        $renderer
            ->expects( $this->once() )
            ->method( "checkContent" )
            ->with( $contentId );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( "exists" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: no template configured" );

        $this->assertEquals(
            null,
            $renderer->renderContentEmbed( $contentId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderContentEmbedNoTemplateFound()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkContent", "getEmbedTemplateName" ) );
        $contentId = 42;
        $viewType = "embedTest";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = false;

        $renderer
            ->expects( $this->once() )
            ->method( "checkContent" )
            ->with( $contentId );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( false ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: template '{$templateName}' does not exists" );

        $this->assertEquals(
            null,
            $renderer->renderContentEmbed( $contentId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderContentEmbedAccessDenied()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkContent", "getEmbedTemplateName" ) );
        $contentId = 42;
        $viewType = "embedTest";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = true;
        $result = "result";

        $renderer
            ->expects( $this->once() )
            ->method( "checkContent" )
            ->with( $contentId )
            ->will( $this->throwException( new AccessDeniedException ) );

        $renderer
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $templateName, $parameters )
            ->will( $this->returnValue( $result ) );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: access denied to embed Content #{$contentId}" );

        $this->assertEquals(
            $result,
            $renderer->renderContentEmbed( $contentId, $viewType, $parameters, $isInline )
        );
    }

    public function providerForTestRenderContentEmbedNotFound()
    {
        return array(
            array( new NotFoundException( "Content", 42 ) ),
            array( new NotFoundHttpException() ),
        );
    }

    /**
     * @dataProvider providerForTestRenderContentEmbedNotFound
     */
    public function testRenderContentEmbedNotFound( Exception $exception )
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkContent", "getEmbedTemplateName" ) );
        $contentId = 42;
        $viewType = "embedTest";
        $parameters = array( "parameters" );
        $isInline = true;
        $result = null;

        $renderer
            ->expects( $this->once() )
            ->method( "checkContent" )
            ->with( $contentId )
            ->will( $this->throwException( $exception ) );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->never() )
            ->method( "getEmbedTemplateName" );

        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( "exists" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Content #{$contentId} not found" );

        $this->assertEquals(
            $result,
            $renderer->renderContentEmbed( $contentId, $viewType, $parameters, $isInline )
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Something threw up
     */
    public function testRenderContentEmbedThrowsException()
    {
        $renderer = $this->getMockedRenderer( array( "checkContent" ) );
        $contentId = 42;

        $renderer
            ->expects( $this->once() )
            ->method( "checkContent" )
            ->with( $contentId )
            ->will( $this->throwException( new Exception( "Something threw up" ) ) );

        $renderer->renderContentEmbed( $contentId, "embedTest", array( "parameters" ), true );
    }

    public function providerForTestRenderContentWithTemplate()
    {
        $contentId = 42;

        return array(
            array(
                false,
                new AccessDeniedException,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content_denied", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName1" ) ),
                ),
                array(
                    array( "error", "Could not render embedded resource: access denied to embed Content #{$contentId}" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                true,
                new AccessDeniedException,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content_inline_denied", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName2" ) ),
                ),
                array(
                    array( "error", "Could not render embedded resource: access denied to embed Content #{$contentId}" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                false,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName3" ) ),
                ),
                array(),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                true,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content_inline", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName4" ) ),
                ),
                array(),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                false,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default", true ),
                    array( "getParameter", $namespace2, array( "template" => $templateName = "templateName5" ) ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                true,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content_inline", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default_inline", true ),
                    array( "getParameter", $namespace2, array( "template" => $templateName = "templateName6" ) ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                false,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default", false ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                    array( "warning", "Embed tag default configuration '{$namespace2}' was not found" ),
                ),
                null,
                null,
                null,
            ),
            array(
                true,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.content_inline", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default_inline", false ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                    array( "warning", "Embed tag default configuration '{$namespace2}' was not found" ),
                ),
                null,
                null,
                null,
            ),
        );
    }

    /**
     * @dataProvider providerForTestRenderContentWithTemplate
     */
    public function testRenderContentWithTemplate(
        $isInline,
        $deniedException,
        array $configResolverParams,
        array $loggerParams,
        $templateEngineTemplate,
        $renderTemplate,
        $renderResult
    )
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkContent" ) );
        $contentId = 42;
        $viewType = "embedTest";
        $parameters = array( "parameters" );

        if ( isset( $deniedException ) )
        {
            $renderer
                ->expects( $this->once() )
                ->method( "checkContent" )
                ->with( $contentId )
                ->will( $this->throwException( $deniedException ) );
        }
        else
        {
            $renderer
                ->expects( $this->once() )
                ->method( "checkContent" )
                ->with( $contentId );
        }

        if ( !isset( $renderTemplate ) )
        {
            $renderer
                ->expects( $this->never() )
                ->method( "render" );
        }
        else
        {
            $renderer
                ->expects( $this->once() )
                ->method( "render" )
                ->with( $renderTemplate, $parameters )
                ->will( $this->returnValue( $renderResult ) );
        }

        if ( !isset( $templateEngineTemplate ) )
        {
            $this->templateEngineMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $this->templateEngineMock
                ->expects( $this->once() )
                ->method( "exists" )
                ->with( $templateEngineTemplate )
                ->will( $this->returnValue( true ) );
        }

        if ( empty( $configResolverParams ) )
        {
            $this->configResolverMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $i = 0;
            foreach ( $configResolverParams as $params )
            {
                $method = $params[0];
                $namespace = $params[1];
                $returnValue = $params[2];
                $this->configResolverMock
                    ->expects( $this->at( $i ) )
                    ->method( $method )
                    ->with( $namespace )
                    ->will( $this->returnValue( $returnValue ) );
                $i++;
            }
        }

        if ( empty( $loggerParams ) )
        {
            $this->loggerMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $i = 0;
            foreach ( $loggerParams as $params )
            {
                $method = $params[0];
                $message = $params[1];
                $this->loggerMock
                    ->expects( $this->at( $i ) )
                    ->method( $method )
                    ->with( $message );
                $i++;
            }
        }

        $this->assertEquals(
            $renderResult,
            $renderer->renderContentEmbed( $contentId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderLocationEmbed()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation", "getEmbedTemplateName" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = false;
        $result = "result";
        $mockLocation = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location" );

        $mockLocation
            ->expects( $this->once() )
            ->method( "__get" )
            ->with( "invisible" )
            ->will( $this->returnValue( false ) );

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->returnValue( $mockLocation ) );

        $renderer
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $templateName, $parameters )
            ->will( $this->returnValue( $result ) );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $this->loggerMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $this->assertEquals(
            $result,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderLocationEmbedNoTemplateConfigured()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation", "getEmbedTemplateName" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $templateName = null;
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = false;
        $mockLocation = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location" );

        $mockLocation
            ->expects( $this->once() )
            ->method( "__get" )
            ->with( "invisible" )
            ->will( $this->returnValue( false ) );

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->returnValue( $mockLocation ) );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( "exists" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: no template configured" );

        $this->assertEquals(
            null,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderLocationEmbedNoTemplateFound()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation", "getEmbedTemplateName" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = false;
        $mockLocation = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location" );

        $mockLocation
            ->expects( $this->once() )
            ->method( "__get" )
            ->with( "invisible" )
            ->will( $this->returnValue( false ) );

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->returnValue( $mockLocation ) );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( false ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: template '{$templateName}' does not exists" );

        $this->assertEquals(
            null,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderLocationEmbedAccessDenied()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation", "getEmbedTemplateName" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $templateName = "templateName";
        $parameters = array( "parameters" );
        $isInline = true;
        $isDenied = true;
        $result = "result";

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->throwException( new AccessDeniedException ) );

        $renderer
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $templateName, $parameters )
            ->will( $this->returnValue( $result ) );

        $renderer
            ->expects( $this->once() )
            ->method( "getEmbedTemplateName" )
            ->with( Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied )
            ->will( $this->returnValue( $templateName ) );

        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( "exists" )
            ->with( $templateName )
            ->will( $this->returnValue( true ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: access denied to embed Location #{$locationId}" );

        $this->assertEquals(
            $result,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    public function testRenderLocationEmbedInvisible()
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation", "getEmbedTemplateName" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $parameters = array( "parameters" );
        $isInline = true;
        $mockLocation = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location" );

        $mockLocation
            ->expects( $this->once() )
            ->method( "__get" )
            ->with( "invisible" )
            ->will( $this->returnValue( true ) );

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->returnValue( $mockLocation ) );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->never() )
            ->method( "getEmbedTemplateName" );

        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( "exists" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Location #{$locationId} is not visible" );

        $this->assertEquals(
            null,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    public function providerForTestRenderLocationEmbedNotFound()
    {
        return array(
            array( new NotFoundException( "Location", 42 ) ),
            array( new NotFoundHttpException() ),
        );
    }

    /**
     * @dataProvider providerForTestRenderLocationEmbedNotFound
     */
    public function testRenderLocationEmbedNotFound( Exception $exception )
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation", "getEmbedTemplateName" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $parameters = array( "parameters" );
        $isInline = true;
        $result = null;

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->throwException( $exception ) );

        $renderer
            ->expects( $this->never() )
            ->method( "render" );

        $renderer
            ->expects( $this->never() )
            ->method( "getEmbedTemplateName" );

        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( "exists" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Location #{$locationId} not found" );

        $this->assertEquals(
            $result,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Something threw up
     */
    public function testRenderLocationEmbedThrowsException()
    {
        $renderer = $this->getMockedRenderer( array( "checkLocation" ) );
        $locationId = 42;

        $renderer
            ->expects( $this->once() )
            ->method( "checkLocation" )
            ->with( $locationId )
            ->will( $this->throwException( new Exception( "Something threw up" ) ) );

        $renderer->renderLocationEmbed( $locationId, "embedTest", array( "parameters" ), true );
    }

    public function providerForTestRenderLocationWithTemplate()
    {
        $locationId = 42;

        return array(
            array(
                false,
                new AccessDeniedException,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location_denied", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName1" ) ),
                ),
                array(
                    array( "error", "Could not render embedded resource: access denied to embed Location #{$locationId}" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                true,
                new AccessDeniedException,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location_inline_denied", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName2" ) ),
                ),
                array(
                    array( "error", "Could not render embedded resource: access denied to embed Location #{$locationId}" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                false,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName3" ) ),
                ),
                array(),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                true,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location_inline", true ),
                    array( "getParameter", $namespace, array( "template" => $templateName = "templateName4" ) ),
                ),
                array(),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                false,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default", true ),
                    array( "getParameter", $namespace2, array( "template" => $templateName = "templateName5" ) ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                true,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location_inline", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default_inline", true ),
                    array( "getParameter", $namespace2, array( "template" => $templateName = "templateName6" ) ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                ),
                $templateName,
                $templateName,
                "result",
            ),
            array(
                false,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default", false ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                    array( "warning", "Embed tag default configuration '{$namespace2}' was not found" ),
                ),
                null,
                null,
                null,
            ),
            array(
                true,
                null,
                array(
                    array( "hasParameter", $namespace = "test.name.space.embed.location_inline", false ),
                    array( "hasParameter", $namespace2 = "test.name.space.embed.default_inline", false ),
                ),
                array(
                    array( "warning", "Embed tag configuration '{$namespace}' was not found" ),
                    array( "warning", "Embed tag default configuration '{$namespace2}' was not found" ),
                ),
                null,
                null,
                null,
            ),
        );
    }

    /**
     * @dataProvider providerForTestRenderLocationWithTemplate
     */
    public function testRenderLocationWithTemplate(
        $isInline,
        $deniedException,
        array $configResolverParams,
        array $loggerParams,
        $templateEngineTemplate,
        $renderTemplate,
        $renderResult
    )
    {
        $renderer = $this->getMockedRenderer( array( "render", "checkLocation" ) );
        $locationId = 42;
        $viewType = "embedTest";
        $parameters = array( "parameters" );
        $mockLocation = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location" );

        if ( isset( $deniedException ) )
        {
            $renderer
                ->expects( $this->once() )
                ->method( "checkLocation" )
                ->with( $locationId )
                ->will( $this->throwException( $deniedException ) );
        }
        else
        {
            $mockLocation
                ->expects( $this->once() )
                ->method( "__get" )
                ->with( "invisible" )
                ->will( $this->returnValue( false ) );

            $renderer
                ->expects( $this->once() )
                ->method( "checkLocation" )
                ->with( $locationId )
                ->will( $this->returnValue( $mockLocation ) );
        }

        if ( !isset( $renderTemplate ) )
        {
            $renderer
                ->expects( $this->never() )
                ->method( "render" );
        }
        else
        {
            $renderer
                ->expects( $this->once() )
                ->method( "render" )
                ->with( $renderTemplate, $parameters )
                ->will( $this->returnValue( $renderResult ) );
        }

        if ( !isset( $templateEngineTemplate ) )
        {
            $this->templateEngineMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $this->templateEngineMock
                ->expects( $this->once() )
                ->method( "exists" )
                ->with( $templateEngineTemplate )
                ->will( $this->returnValue( true ) );
        }

        if ( empty( $configResolverParams ) )
        {
            $this->configResolverMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $i = 0;
            foreach ( $configResolverParams as $params )
            {
                $method = $params[0];
                $namespace = $params[1];
                $returnValue = $params[2];
                $this->configResolverMock
                    ->expects( $this->at( $i ) )
                    ->method( $method )
                    ->with( $namespace )
                    ->will( $this->returnValue( $returnValue ) );
                $i++;
            }
        }

        if ( empty( $loggerParams ) )
        {
            $this->loggerMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else
        {
            $i = 0;
            foreach ( $loggerParams as $params )
            {
                $method = $params[0];
                $message = $params[1];
                $this->loggerMock
                    ->expects( $this->at( $i ) )
                    ->method( $method )
                    ->with( $message );
                $i++;
            }
        }

        $this->assertEquals(
            $renderResult,
            $renderer->renderLocationEmbed( $locationId, $viewType, $parameters, $isInline )
        );
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\MVC\Symfony\FieldType\RichText\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedRenderer( array $methods = array() )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\RichText\\Renderer",
            $methods,
            array(
                $this->repositoryMock,
                $this->securityContextMock,
                $this->configResolverMock,
                $this->templateEngineMock,
                "test.name.space.tag",
                "test.name.space.embed",
                $this->loggerMock
            )
        );
    }

    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        return $this->getMock(
            "eZ\\Publish\\API\\Repository\\Repository"
        );
    }

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityContextMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSecurityContextMock()
    {
        return $this->getMock(
            "Symfony\\Component\\Security\\Core\\SecurityContextInterface"
        );
    }

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolverMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigResolverMock()
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\MVC\\ConfigResolverInterface"
        );
    }

    /**
     * @var \Symfony\Component\Templating\EngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateEngineMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTemplateEngineMock()
    {
        return $this->getMock(
            "Symfony\\Component\\Templating\\EngineInterface"
        );
    }

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoggerMock()
    {
        return $this->getMock(
            "Psr\\Log\\LoggerInterface"
        );
    }
}
