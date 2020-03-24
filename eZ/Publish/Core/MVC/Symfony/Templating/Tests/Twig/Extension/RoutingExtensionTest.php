<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\RoutingExtension;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Test\IntegrationTestCase;

final class RoutingExtensionTest extends IntegrationTestCase
{
    protected function getExtensions(): array
    {
        return [
            new RoutingExtension(
                $this->getRouteReferenceGenerator(),
                $this->getUrlGenerator()
            ),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/_fixtures/routing_functions';
    }

    protected function getExampleContent(int $id): APIContent
    {
        return new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => $this->getExampleContentInfo($id),
            ]),
        ]);
    }

    protected function getExampleContentInfo(int $id): ContentInfo
    {
        return new ContentInfo([
            'id' => $id,
        ]);
    }

    protected function getExampleLocation(int $id): APILocation
    {
        return new Location(['id' => $id]);
    }

    protected function getExampleRouteReference($name, array $parameters = []): RouteReference
    {
        return new RouteReference($name, $parameters);
    }

    protected function getExampleUnsupportedObject(): object
    {
        $object = new stdClass();
        $object->foo = 'foo';
        $object->bar = 'bar';

        return $object;
    }

    private function getRouteReferenceGenerator(): RouteReferenceGeneratorInterface
    {
        $generator = new RouteReferenceGenerator(
            $this->createMock(EventDispatcherInterface::class)
        );
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $generator->setRequestStack($requestStack);

        return $generator;
    }

    private function getUrlGenerator(): UrlGeneratorInterface
    {
        $generator = $this->createMock(UrlGeneratorInterface::class);
        $generator
            ->method('generate')
            ->willReturnCallback(static function ($name, $parameters, $referenceType): string {
                return json_encode([
                    '$name' => $name,
                    '$parameters' => $parameters,
                    '$referenceType' => $referenceType,
                ]);
            });

        return $generator;
    }
}
