<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderContentStrategy;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use Symfony\Component\HttpFoundation\Request;

class RenderContentStrategyTest extends BaseRenderStrategyTest
{
    public function testUnsupportedValueObject(): void
    {
        $renderContentStrategy = $this->createRenderStrategy(
            RenderContentStrategy::class,
            [
                $this->createRenderMethod(),
            ]
        );

        $valueObject = new class() extends ValueObject {
        };
        $this->assertFalse($renderContentStrategy->supports($valueObject));

        $this->expectException(InvalidArgumentException::class);
        $renderContentStrategy->render($valueObject, new RenderOptions());
    }

    public function testDefaultRenderMethod(): void
    {
        $renderContentStrategy = $this->createRenderStrategy(
            RenderContentStrategy::class,
            [
                $this->createRenderMethod('inline'),
            ],
            'inline'
        );

        $contentMock = $this->createMock(Content::class);
        $this->assertTrue($renderContentStrategy->supports($contentMock));

        $this->assertSame(
            'inline_rendered',
            $renderContentStrategy->render($contentMock, new RenderOptions())
        );
    }

    public function testUnknownRenderMethod(): void
    {
        $renderContentStrategy = $this->createRenderStrategy(
            RenderContentStrategy::class,
            [],
        );

        $contentMock = $this->createMock(Content::class);
        $this->assertTrue($renderContentStrategy->supports($contentMock));

        $this->expectException(InvalidArgumentException::class);
        $renderContentStrategy->render($contentMock, new RenderOptions());
    }

    public function testMultipleRenderMethods(): void
    {
        $renderContentStrategy = $this->createRenderStrategy(
            RenderContentStrategy::class,
            [
                $this->createRenderMethod('method_a'),
                $this->createRenderMethod('method_b'),
                $this->createRenderMethod('method_c'),
            ],
        );

        $contentMock = $this->createMock(Content::class);
        $this->assertTrue($renderContentStrategy->supports($contentMock));

        $this->assertSame(
            'method_b_rendered',
            $renderContentStrategy->render($contentMock, new RenderOptions([
                'method' => 'method_b',
            ]))
        );
    }

    public function testExpectedMethodRenderRequestFormat(): void
    {
        $request = new Request();
        $request->headers->set('Surrogate-Capability', 'TEST/1.0');

        $renderMethodMock = $this->createMock(RenderMethod::class);
        $renderMethodMock
            ->method('getIdentifier')
            ->willReturn('method_b');

        $siteAccess = new SiteAccess('some_siteaccess');

        $content = $this->createContent(123);

        $renderMethodMock
            ->expects($this->once())
            ->method('render')
            ->with($this->callback(function (Request $request) use ($siteAccess, $content): bool {
                $headers = $request->headers;
                $this->assertSame('some_siteaccess', $headers->get('siteaccess'));
                $this->assertSame('TEST/1.0', $headers->get('Surrogate-Capability'));

                $attributes = $request->attributes;
                $this->assertSame('_ez_content_view', $attributes->get('_route'));
                $this->assertSame('ez_content::viewAction', $attributes->get('_controller'));
                $this->assertEquals($siteAccess, $attributes->get('siteaccess'));
                $this->assertSame($content->id, $attributes->get('contentId'));
                $this->assertSame('awesome', $attributes->get('viewType'));

                return true;
            }))
            ->willReturn('some_rendered_content');

        $renderContentStrategy = $this->createRenderStrategy(
            RenderContentStrategy::class,
            [
                $this->createRenderMethod('method_a'),
                $renderMethodMock,
                $this->createRenderMethod('method_c'),
            ],
            'method_a',
            $siteAccess->name,
            $request
        );

        $this->assertSame('some_rendered_content', $renderContentStrategy->render(
            $content,
            new RenderOptions([
                'method' => 'method_b',
                'viewType' => 'awesome',
            ])
        ));
    }
}
