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
use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use Symfony\Component\HttpFoundation\Request;

class RenderLocationStrategyTest extends BaseRenderStrategyTest
{
    public function testUnsupportedValueObject(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createRenderMethod(),
            ]
        );

        $valueObject = new class() extends ValueObject {
        };
        $this->assertFalse($renderLocationStrategy->supports($valueObject));

        $this->expectException(InvalidArgumentException::class);
        $renderLocationStrategy->render($valueObject, new RenderOptions());
    }

    public function testDefaultRenderMethod(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createRenderMethod('inline'),
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

    public function testUnknownRenderMethod(): void
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

    public function testMultipleRenderMethods(): void
    {
        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createRenderMethod('method_a'),
                $this->createRenderMethod('method_b'),
                $this->createRenderMethod('method_c'),
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

        $renderMethodMock = $this->createMock(RenderMethod::class);
        $renderMethodMock
            ->method('getIdentifier')
            ->willReturn('method_b');

        $siteAccess = new SiteAccess('some_siteaccess');

        $content = $this->createContent(234);
        $location = $this->createLocation($content, 345);

        $renderMethodMock
            ->expects($this->once())
            ->method('render')
            ->with($this->callback(function (Request $request) use ($siteAccess, $content, $location): bool {
                $headers = $request->headers;
                $this->assertSame('some_siteaccess', $headers->get('siteaccess'));
                $this->assertSame('TEST/1.0', $headers->get('Surrogate-Capability'));

                $attributes = $request->attributes;
                $this->assertSame('_ez_content_view', $attributes->get('_route'));
                $this->assertSame('ez_content::viewAction', $attributes->get('_controller'));
                $this->assertEquals($siteAccess, $attributes->get('siteaccess'));
                $this->assertSame($content->id, $attributes->get('contentId'));
                $this->assertSame($location->id, $attributes->get('locationId'));
                $this->assertSame('awesome', $attributes->get('viewType'));

                return true;
            }))
            ->willReturn('some_rendered_content');

        $renderLocationStrategy = $this->createRenderStrategy(
            RenderLocationStrategy::class,
            [
                $this->createRenderMethod('method_a'),
                $renderMethodMock,
                $this->createRenderMethod('method_c'),
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
