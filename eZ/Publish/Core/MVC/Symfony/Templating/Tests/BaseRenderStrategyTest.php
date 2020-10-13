<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Search\Tests\TestCase;
use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use eZ\Publish\SPI\MVC\Templating\RenderStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseRenderStrategyTest extends TestCase
{
    public function createRenderStrategy(
        string $typeClass,
        array $renderMethods,
        string $defaultMethod = 'inline',
        string $siteAccessName = 'default',
        Request $request = null
    ): RenderStrategy {
        $siteAccess = new SiteAccess($siteAccessName);

        $requestStack = new RequestStack();
        $requestStack->push($request ?? new Request());

        return new $typeClass(
            $renderMethods,
            $defaultMethod,
            $siteAccess,
            $requestStack
        );
    }

    public function createRenderMethod(
        string $identifier = 'inline',
        string $rendered = null
    ): RenderMethod {
        return new class($identifier, $rendered) implements RenderMethod {
            /** @var string */
            private $identifier;

            /** @var string */
            private $rendered;

            public function __construct(
                string $identifier,
                ?string $rendered
            ) {
                $this->identifier = $identifier;
                $this->rendered = $rendered;
            }

            public function getIdentifier(): string
            {
                return $this->identifier;
            }

            public function render(Request $request): string
            {
                return $this->rendered ?? $this->identifier . '_rendered';
            }
        };
    }

    public function createLocation(APIContent $content, int $id): APILocation
    {
        return new Location([
            'id' => $id,
            'contentInfo' => $content->versionInfo->contentInfo,
            'content' => $content,
        ]);
    }

    public function createContent(int $id): APIContent
    {
        return new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => $id,
                ]),
            ]),
        ]);
    }
}
