<?php

/**
 * File containing the RendererTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\RichText;

use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\FieldType\RichText\Renderer;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Templating\EngineInterface;

class RendererTest extends TestCase
{
    public function setUp()
    {
        $this->repositoryMock = $this->getRepositoryMock();
        $this->authorizationCheckerMock = $this->getAuthorizationCheckerMock();
        $this->configResolverMock = $this->getConfigResolverMock();
        $this->templateEngineMock = $this->getTemplateEngineMock();
        $this->loggerMock = $this->getLoggerMock();
        parent::setUp();
    }

    public function testRenderTag()
    {
        $renderer = $this->getMockedRenderer(['render', 'getTagTemplateName']);
        $name = 'tag';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $result = 'result';

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($templateName, $parameters)
            ->will($this->returnValue($result));

        $renderer
            ->expects($this->once())
            ->method('getTagTemplateName')
            ->with($name, $isInline)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(true));

        $this->loggerMock
            ->expects($this->never())
            ->method($this->anything());

        $this->assertEquals(
            $result,
            $renderer->renderTemplate($name, 'tag', $parameters, $isInline)
        );
    }

    public function testRenderTagNoTemplateConfigured()
    {
        $renderer = $this->getMockedRenderer(['render', 'getTagTemplateName']);
        $name = 'tag';
        $parameters = ['parameters'];
        $isInline = true;

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->once())
            ->method('getTagTemplateName')
            ->with($name, $isInline)
            ->will($this->returnValue(null));

        $this->templateEngineMock
            ->expects($this->never())
            ->method('exists');

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render template tag '{$name}': no template configured");

        $this->assertNull(
            $renderer->renderTemplate($name, 'tag', $parameters, $isInline)
        );
    }

    public function testRenderTagNoTemplateFound()
    {
        $renderer = $this->getMockedRenderer(['render', 'getTagTemplateName']);
        $name = 'tag';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->once())
            ->method('getTagTemplateName')
            ->with($name, $isInline)
            ->will($this->returnValue('templateName'));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(false));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render template tag '{$name}': template '{$templateName}' does not exist");

        $this->assertNull(
            $renderer->renderTemplate($name, 'tag', $parameters, $isInline)
        );
    }

    public function providerForTestRenderTagWithTemplate()
    {
        return [
            [
                $tagName = 'tag1',
                [
                    ['hasParameter', $namespace = "test.name.space.tag.{$tagName}", true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName1']],
                ],
                [],
                $templateName,
                $templateName,
                false,
                'result',
            ],
            [
                $tagName = 'tag2',
                [
                    ['hasParameter', $namespace = "test.name.space.tag.{$tagName}", true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName2']],
                ],
                [],
                $templateName,
                $templateName,
                true,
                'result',
            ],
            [
                $tagName = 'tag3',
                [
                    ['hasParameter', "test.name.space.tag.{$tagName}", false],
                    ['hasParameter', $namespace = 'test.name.space.tag.default', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName3']],
                ],
                [
                    ['warning', "Template tag '{$tagName}' configuration was not found"],
                ],
                $templateName,
                $templateName,
                false,
                'result',
            ],
            [
                $tagName = 'tag4',
                [
                    ['hasParameter', "test.name.space.tag.{$tagName}", false],
                    ['hasParameter', $namespace = 'test.name.space.tag.default_inline', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName4']],
                ],
                [
                    ['warning', "Template tag '{$tagName}' configuration was not found"],
                ],
                $templateName,
                $templateName,
                true,
                'result',
            ],
            [
                $tagName = 'tag5',
                [
                    ['hasParameter', "test.name.space.tag.{$tagName}", false],
                    ['hasParameter', $namespace = 'test.name.space.tag.default', false],
                ],
                [
                    ['warning', "Template tag '{$tagName}' configuration was not found"],
                    ['warning', "Template tag '{$tagName}' default configuration was not found"],
                    ['error', "Could not render template tag '{$tagName}': no template configured"],
                ],
                null,
                null,
                false,
                null,
            ],
            [
                $tagName = 'tag6',
                [
                    ['hasParameter', "test.name.space.tag.{$tagName}", false],
                    ['hasParameter', $namespace = 'test.name.space.tag.default_inline', false],
                ],
                [
                    ['warning', "Template tag '{$tagName}' configuration was not found"],
                    ['warning', "Template tag '{$tagName}' default configuration was not found"],
                    ['error', "Could not render template tag '{$tagName}': no template configured"],
                ],
                null,
                null,
                true,
                null,
            ],
        ];
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
    ) {
        $renderer = $this->getMockedRenderer(['render']);
        $parameters = ['parameters'];

        if (!isset($renderTemplate)) {
            $renderer
                ->expects($this->never())
                ->method('render');
        } else {
            $renderer
                ->expects($this->once())
                ->method('render')
                ->with($renderTemplate, $parameters)
                ->will($this->returnValue($renderResult));
        }

        if (!isset($templateEngineTemplate)) {
            $this->templateEngineMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $this->templateEngineMock
                ->expects($this->once())
                ->method('exists')
                ->with($templateEngineTemplate)
                ->will($this->returnValue(true));
        }

        if (empty($configResolverParams)) {
            $this->configResolverMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $i = 0;
            foreach ($configResolverParams as $params) {
                $method = $params[0];
                $namespace = $params[1];
                $returnValue = $params[2];
                $this->configResolverMock
                    ->expects($this->at($i))
                    ->method($method)
                    ->with($namespace)
                    ->will($this->returnValue($returnValue));
                ++$i;
            }
        }

        if (empty($loggerParams)) {
            $this->loggerMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $i = 0;
            foreach ($loggerParams as $params) {
                $method = $params[0];
                $message = $params[1];
                $this->loggerMock
                    ->expects($this->at($i))
                    ->method($method)
                    ->with($message);
                ++$i;
            }
        }

        $this->assertEquals(
            $renderResult,
            $renderer->renderTemplate($tagName, 'tag', $parameters, $isInline)
        );
    }

    public function testRenderContentEmbed()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkContentPermissions', 'getEmbedTemplateName']);
        $contentId = 42;
        $viewType = 'embedTest';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = false;
        $result = 'result';
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('checkContentPermissions')
            ->with($contentMock)
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($templateName, $parameters)
            ->will($this->returnValue($result));

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(true));

        $this->loggerMock
            ->expects($this->never())
            ->method($this->anything());

        $this->assertEquals(
            $result,
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderContentEmbedNoTemplateConfigured()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkContentPermissions', 'getEmbedTemplateName']);
        $contentId = 42;
        $viewType = 'embedTest';
        $templateName = null;
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = false;
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('checkContentPermissions')
            ->with($contentMock)
            ->willReturn($contentMock);

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->never())
            ->method('exists');

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with('Could not render embedded resource: no template configured');

        $this->assertNull(
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderContentEmbedNoTemplateFound()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkContentPermissions', 'getEmbedTemplateName']);
        $contentId = 42;
        $viewType = 'embedTest';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = false;
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('checkContentPermissions')
            ->with($contentMock);

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(false));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: template '{$templateName}' does not exists");

        $this->assertNull(
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderContentEmbedAccessDenied()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkContentPermissions', 'getEmbedTemplateName']);
        $contentId = 42;
        $viewType = 'embedTest';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = true;
        $result = 'result';
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('checkContentPermissions')
            ->with($contentMock)
            ->will($this->throwException(new AccessDeniedException()));

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($templateName, $parameters)
            ->will($this->returnValue($result));

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_CONTENT, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(true));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: access denied to embed Content #{$contentId}");

        $this->assertEquals(
            $result,
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderContentEmbedTrashed()
    {
        $renderer = $this->getMockedRenderer(['checkContentPermissions']);
        $contentId = 42;
        $viewType = 'embedTest';
        $parameters = ['parameters'];
        $isInline = true;

        $contentInfoMock = $this->createMock(ContentInfo::class);
        $contentInfoMock
            ->expects($this->once())
            ->method('__get')
            ->with('mainLocationId')
            ->willReturn(null);

        $contentMock = $this->createMock(Content::class);
        $contentMock
            ->expects($this->once())
            ->method('__get')
            ->with('contentInfo')
            ->willReturn($contentInfoMock);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: Content #{$contentId} is trashed.");

        $this->assertNull(
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    public function providerForTestRenderContentEmbedNotFound()
    {
        return [
            [new NotFoundException('Content', 42)],
            [new NotFoundHttpException()],
        ];
    }

    /**
     * @dataProvider providerForTestRenderContentEmbedNotFound
     */
    public function testRenderContentEmbedNotFound(Exception $exception)
    {
        $renderer = $this->getMockedRenderer(['render', 'checkContentPermissions', 'getEmbedTemplateName']);
        $contentId = 42;
        $viewType = 'embedTest';
        $parameters = ['parameters'];
        $isInline = true;
        $result = null;
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('checkContentPermissions')
            ->with($contentMock)
            ->will($this->throwException($exception));

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->never())
            ->method('getEmbedTemplateName');

        $this->templateEngineMock
            ->expects($this->never())
            ->method('exists');

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: Content #{$contentId} not found");

        $this->assertEquals(
            $result,
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Something threw up
     */
    public function testRenderContentEmbedThrowsException()
    {
        $renderer = $this->getMockedRenderer(['checkContentPermissions']);
        $contentId = 42;
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        $renderer
            ->expects($this->once())
            ->method('checkContentPermissions')
            ->with($contentMock)
            ->will($this->throwException(new Exception('Something threw up')));

        $renderer->renderContentEmbed($contentId, 'embedTest', ['parameters'], true);
    }

    public function providerForTestRenderContentWithTemplate()
    {
        $contentId = 42;

        return [
            [
                false,
                new AccessDeniedException(),
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content_denied', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName1']],
                ],
                [
                    ['error', "Could not render embedded resource: access denied to embed Content #{$contentId}"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                true,
                new AccessDeniedException(),
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content_inline_denied', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName2']],
                ],
                [
                    ['error', "Could not render embedded resource: access denied to embed Content #{$contentId}"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                false,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName3']],
                ],
                [],
                $templateName,
                $templateName,
                'result',
            ],
            [
                true,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content_inline', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName4']],
                ],
                [],
                $templateName,
                $templateName,
                'result',
            ],
            [
                false,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default', true],
                    ['getParameter', $namespace2, ['template' => $templateName = 'templateName5']],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                true,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content_inline', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default_inline', true],
                    ['getParameter', $namespace2, ['template' => $templateName = 'templateName6']],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                false,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default', false],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                    ['warning', "Embed tag default configuration '{$namespace2}' was not found"],
                ],
                null,
                null,
                null,
            ],
            [
                true,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.content_inline', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default_inline', false],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                    ['warning', "Embed tag default configuration '{$namespace2}' was not found"],
                ],
                null,
                null,
                null,
            ],
        ];
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
    ) {
        $renderer = $this->getMockedRenderer(['render', 'checkContentPermissions']);
        $contentId = 42;
        $viewType = 'embedTest';
        $parameters = ['parameters'];
        $mainLocationId = 2;

        $contentMock = $this->getContentMock($mainLocationId);

        $this->repositoryMock
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($contentMock);

        if (isset($deniedException)) {
            $renderer
                ->expects($this->once())
                ->method('checkContentPermissions')
                ->with($contentMock)
                ->will($this->throwException($deniedException));
        } else {
            $renderer
                ->expects($this->once())
                ->method('checkContentPermissions')
                ->with($contentMock)
                ->willReturn($contentMock);
        }

        if (!isset($renderTemplate)) {
            $renderer
                ->expects($this->never())
                ->method('render');
        } else {
            $renderer
                ->expects($this->once())
                ->method('render')
                ->with($renderTemplate, $parameters)
                ->will($this->returnValue($renderResult));
        }

        if (!isset($templateEngineTemplate)) {
            $this->templateEngineMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $this->templateEngineMock
                ->expects($this->once())
                ->method('exists')
                ->with($templateEngineTemplate)
                ->will($this->returnValue(true));
        }

        if (empty($configResolverParams)) {
            $this->configResolverMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $i = 0;
            foreach ($configResolverParams as $params) {
                $method = $params[0];
                $namespace = $params[1];
                $returnValue = $params[2];
                $this->configResolverMock
                    ->expects($this->at($i))
                    ->method($method)
                    ->with($namespace)
                    ->will($this->returnValue($returnValue));
                ++$i;
            }
        }

        if (empty($loggerParams)) {
            $this->loggerMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $i = 0;
            foreach ($loggerParams as $params) {
                $method = $params[0];
                $message = $params[1];
                $this->loggerMock
                    ->expects($this->at($i))
                    ->method($method)
                    ->with($message);
                ++$i;
            }
        }

        $this->assertEquals(
            $renderResult,
            $renderer->renderContentEmbed($contentId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderLocationEmbed()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation', 'getEmbedTemplateName']);
        $locationId = 42;
        $viewType = 'embedTest';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = false;
        $result = 'result';
        $mockLocation = $this->createMock(Location::class);

        $mockLocation
            ->expects($this->once())
            ->method('__get')
            ->with('invisible')
            ->will($this->returnValue(false));

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->returnValue($mockLocation));

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($templateName, $parameters)
            ->will($this->returnValue($result));

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(true));

        $this->loggerMock
            ->expects($this->never())
            ->method($this->anything());

        $this->assertEquals(
            $result,
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderLocationEmbedNoTemplateConfigured()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation', 'getEmbedTemplateName']);
        $locationId = 42;
        $viewType = 'embedTest';
        $templateName = null;
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = false;
        $mockLocation = $this->createMock(Location::class);

        $mockLocation
            ->expects($this->once())
            ->method('__get')
            ->with('invisible')
            ->will($this->returnValue(false));

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->returnValue($mockLocation));

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->never())
            ->method('exists');

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with('Could not render embedded resource: no template configured');

        $this->assertNull(
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderLocationEmbedNoTemplateFound()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation', 'getEmbedTemplateName']);
        $locationId = 42;
        $viewType = 'embedTest';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = false;
        $mockLocation = $this->createMock(Location::class);

        $mockLocation
            ->expects($this->once())
            ->method('__get')
            ->with('invisible')
            ->will($this->returnValue(false));

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->returnValue($mockLocation));

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(false));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: template '{$templateName}' does not exists");

        $this->assertNull(
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderLocationEmbedAccessDenied()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation', 'getEmbedTemplateName']);
        $locationId = 42;
        $viewType = 'embedTest';
        $templateName = 'templateName';
        $parameters = ['parameters'];
        $isInline = true;
        $isDenied = true;
        $result = 'result';

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->throwException(new AccessDeniedException()));

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($templateName, $parameters)
            ->will($this->returnValue($result));

        $renderer
            ->expects($this->once())
            ->method('getEmbedTemplateName')
            ->with(Renderer::RESOURCE_TYPE_LOCATION, $isInline, $isDenied)
            ->will($this->returnValue($templateName));

        $this->templateEngineMock
            ->expects($this->once())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(true));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: access denied to embed Location #{$locationId}");

        $this->assertEquals(
            $result,
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    public function testRenderLocationEmbedInvisible()
    {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation', 'getEmbedTemplateName']);
        $locationId = 42;
        $viewType = 'embedTest';
        $parameters = ['parameters'];
        $isInline = true;
        $mockLocation = $this->createMock(Location::class);

        $mockLocation
            ->expects($this->once())
            ->method('__get')
            ->with('invisible')
            ->will($this->returnValue(true));

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->returnValue($mockLocation));

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->never())
            ->method('getEmbedTemplateName');

        $this->templateEngineMock
            ->expects($this->never())
            ->method('exists');

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: Location #{$locationId} is not visible");

        $this->assertNull(
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    public function providerForTestRenderLocationEmbedNotFound()
    {
        return [
            [new NotFoundException('Location', 42)],
            [new NotFoundHttpException()],
        ];
    }

    /**
     * @dataProvider providerForTestRenderLocationEmbedNotFound
     */
    public function testRenderLocationEmbedNotFound(Exception $exception)
    {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation', 'getEmbedTemplateName']);
        $locationId = 42;
        $viewType = 'embedTest';
        $parameters = ['parameters'];
        $isInline = true;
        $result = null;

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->throwException($exception));

        $renderer
            ->expects($this->never())
            ->method('render');

        $renderer
            ->expects($this->never())
            ->method('getEmbedTemplateName');

        $this->templateEngineMock
            ->expects($this->never())
            ->method('exists');

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with("Could not render embedded resource: Location #{$locationId} not found");

        $this->assertEquals(
            $result,
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Something threw up
     */
    public function testRenderLocationEmbedThrowsException()
    {
        $renderer = $this->getMockedRenderer(['checkLocation']);
        $locationId = 42;

        $renderer
            ->expects($this->once())
            ->method('checkLocation')
            ->with($locationId)
            ->will($this->throwException(new Exception('Something threw up')));

        $renderer->renderLocationEmbed($locationId, 'embedTest', ['parameters'], true);
    }

    public function providerForTestRenderLocationWithTemplate()
    {
        $locationId = 42;

        return [
            [
                false,
                new AccessDeniedException(),
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location_denied', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName1']],
                ],
                [
                    ['error', "Could not render embedded resource: access denied to embed Location #{$locationId}"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                true,
                new AccessDeniedException(),
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location_inline_denied', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName2']],
                ],
                [
                    ['error', "Could not render embedded resource: access denied to embed Location #{$locationId}"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                false,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName3']],
                ],
                [],
                $templateName,
                $templateName,
                'result',
            ],
            [
                true,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location_inline', true],
                    ['getParameter', $namespace, ['template' => $templateName = 'templateName4']],
                ],
                [],
                $templateName,
                $templateName,
                'result',
            ],
            [
                false,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default', true],
                    ['getParameter', $namespace2, ['template' => $templateName = 'templateName5']],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                true,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location_inline', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default_inline', true],
                    ['getParameter', $namespace2, ['template' => $templateName = 'templateName6']],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                ],
                $templateName,
                $templateName,
                'result',
            ],
            [
                false,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default', false],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                    ['warning', "Embed tag default configuration '{$namespace2}' was not found"],
                ],
                null,
                null,
                null,
            ],
            [
                true,
                null,
                [
                    ['hasParameter', $namespace = 'test.name.space.embed.location_inline', false],
                    ['hasParameter', $namespace2 = 'test.name.space.embed.default_inline', false],
                ],
                [
                    ['warning', "Embed tag configuration '{$namespace}' was not found"],
                    ['warning', "Embed tag default configuration '{$namespace2}' was not found"],
                ],
                null,
                null,
                null,
            ],
        ];
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
    ) {
        $renderer = $this->getMockedRenderer(['render', 'checkLocation']);
        $locationId = 42;
        $viewType = 'embedTest';
        $parameters = ['parameters'];
        $mockLocation = $this->createMock(Location::class);

        if (isset($deniedException)) {
            $renderer
                ->expects($this->once())
                ->method('checkLocation')
                ->with($locationId)
                ->will($this->throwException($deniedException));
        } else {
            $mockLocation
                ->expects($this->once())
                ->method('__get')
                ->with('invisible')
                ->will($this->returnValue(false));

            $renderer
                ->expects($this->once())
                ->method('checkLocation')
                ->with($locationId)
                ->will($this->returnValue($mockLocation));
        }

        if (!isset($renderTemplate)) {
            $renderer
                ->expects($this->never())
                ->method('render');
        } else {
            $renderer
                ->expects($this->once())
                ->method('render')
                ->with($renderTemplate, $parameters)
                ->will($this->returnValue($renderResult));
        }

        if (!isset($templateEngineTemplate)) {
            $this->templateEngineMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $this->templateEngineMock
                ->expects($this->once())
                ->method('exists')
                ->with($templateEngineTemplate)
                ->will($this->returnValue(true));
        }

        if (empty($configResolverParams)) {
            $this->configResolverMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $i = 0;
            foreach ($configResolverParams as $params) {
                $method = $params[0];
                $namespace = $params[1];
                $returnValue = $params[2];
                $this->configResolverMock
                    ->expects($this->at($i))
                    ->method($method)
                    ->with($namespace)
                    ->will($this->returnValue($returnValue));
                ++$i;
            }
        }

        if (empty($loggerParams)) {
            $this->loggerMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $i = 0;
            foreach ($loggerParams as $params) {
                $method = $params[0];
                $message = $params[1];
                $this->loggerMock
                    ->expects($this->at($i))
                    ->method($method)
                    ->with($message);
                ++$i;
            }
        }

        $this->assertEquals(
            $renderResult,
            $renderer->renderLocationEmbed($locationId, $viewType, $parameters, $isInline)
        );
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\MVC\Symfony\FieldType\RichText\Renderer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockedRenderer(array $methods = [])
    {
        return $this->getMockBuilder(Renderer::class)
            ->setConstructorArgs(
                [
                    $this->repositoryMock,
                    $this->authorizationCheckerMock,
                    $this->configResolverMock,
                    $this->templateEngineMock,
                    'test.name.space.tag',
                    'test.name.space.style',
                    'test.name.space.embed',
                    $this->loggerMock,
                ]
            )
            ->setMethods($methods)
            ->getMock();
    }

    /** @var \eZ\Publish\API\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject */
    protected $repositoryMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryMock()
    {
        return $this->createMock(Repository::class);
    }

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationCheckerMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAuthorizationCheckerMock()
    {
        return $this->createMock(AuthorizationCheckerInterface::class);
    }

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $configResolverMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConfigResolverMock()
    {
        return $this->createMock(ConfigResolverInterface::class);
    }

    /** @var \Symfony\Component\Templating\EngineInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $templateEngineMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTemplateEngineMock()
    {
        return $this->createMock(EngineInterface::class);
    }

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    protected function getContentMock($mainLocationId)
    {
        $contentInfoMock = $this->createMock(ContentInfo::class);
        $contentInfoMock
            ->expects($this->once())
            ->method('__get')
            ->with('mainLocationId')
            ->willReturn($mainLocationId);

        $contentMock = $this->createMock(Content::class);
        $contentMock
            ->expects($this->once())
            ->method('__get')
            ->with('contentInfo')
            ->willReturn($contentInfoMock);

        return $contentMock;
    }
}
