<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderLocationStrategy;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

class RenderLocationStrategyTest extends BaseRenderStrategyTest
{
    public function testUnsupportedValueObject(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createFragmentRenderer(),
            ]
        );

        $valueObject = new class() extends ValueObject {
        };
        $this->assertFalse($renderLocationStrategy->supports($valueObject));

        $this->expectException(InvalidArgumentException::class);
        $renderLocationStrategy->render($valueObject, new RenderOptions());
    }

    public function testDefaultFragmentRenderer(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createFragmentRenderer('inline'),
            ],
            'inline'
        );

        $locationMock = $this->createMock(Location::class);
        $this->assertTrue($renderLocationStrategy->supports($locationMock));

        $this->assertSame(
            'inline_rendered',
            $renderLocationStrategy->render($locationMock, new RenderOptions())
        );
    }

    public function testUnknownFragmentRenderer(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [],
        );

        $locationMock = $this->createMock(Location::class);
        $this->assertTrue($renderLocationStrategy->supports($locationMock));

        $this->expectException(InvalidArgumentException::class);
        $renderLocationStrategy->render($locationMock, new RenderOptions());
    }

    public function testMultipleFragmentRenderers(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createFragmentRenderer('method_a'),
                $this->createFragmentRenderer('method_b'),
                $this->createFragmentRenderer('method_c'),
            ],
        );

        $locationMock = $this->createMock(Location::class);
        $this->assertTrue($renderLocationStrategy->supports($locationMock));

        $this->assertSame(
            'method_b_rendered',
            $renderLocationStrategy->render($locationMock, new RenderOptions([
                'method' => 'method_b',
            ]))
        );
    }

    public function testExpectedMethodRenderRequestFormat(): void
    {
        $request = new Request();
        $request->headers->set('Surrogate-Capability', 'TEST/1.0');

        $siteAccess = new SiteAccess('some_siteaccess');
        $content = $this->createContent(234);
        $location = $this->createLocation($content, 345);

        $fragmentRendererMock = $this->createMock(FragmentRendererInterface::class);
        $fragmentRendererMock
            ->method('getName')
            ->willReturn('method_b');

        $controllerReferenceCallback = $this->callback(function (ControllerReference $controllerReference) {
            $this->assertInstanceOf(ControllerReference::class, $controllerReference);
            $this->assertEquals('ez_content::viewAction', $controllerReference->controller);
            $this->assertSame([
                'contentId' => 234,
                'locationId' => 345,
                'viewType' => 'awesome',
            ], $controllerReference->attributes);

            return true;
        });

        $requestCallback = $this->callback(function (Request $request) use ($siteAccess, $content): bool {
            $this->assertSame('TEST/1.0', $request->headers->get('Surrogate-Capability'));

            return true;
        });

        $fragmentRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($controllerReferenceCallback, $requestCallback)
            ->willReturn(new Response('some_rendered_content'));

        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createFragmentRenderer('method_a'),
                $fragmentRendererMock,
                $this->createFragmentRenderer('method_c'),
            ],
            'method_a',
            $siteAccess->name,
            $request
        );

        $this->assertSame('some_rendered_content', $renderLocationStrategy->render(
            $location,
            new RenderOptions([
                'method' => 'method_b',
                'viewType' => 'awesome',
            ])
        ));
    }
}
