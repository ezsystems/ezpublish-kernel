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
use eZ\Publish\SPI\MVC\Templating\RenderStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

abstract class BaseRenderStrategyTest extends TestCase
{
    public function createRenderStrategy(
        string $typeClass,
        array $fragmentRenderers,
        string $defaultMethod = 'inline',
        string $siteAccessName = 'default',
        Request $request = null
    ): RenderStrategy {
        $siteAccess = new SiteAccess($siteAccessName);

        $requestStack = new RequestStack();
        $requestStack->push($request ?? new Request());

        return new $typeClass(
            $fragmentRenderers,
            $defaultMethod,
            $siteAccess,
            $requestStack
        );
    }

    public function createFragmentRenderer(
        string $name = 'inline',
        string $rendered = null
    ): FragmentRendererInterface {
        return new class($name, $rendered) implements FragmentRendererInterface {
            /** @var string */
            private $name;

            /** @var string */
            private $rendered;

            public function __construct(
                string $name,
                ?string $rendered
            ) {
                $this->name = $name;
                $this->rendered = $rendered;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function render(
                $uri,
                Request $request,
                array $options = []
            ): Response {
                return new Response($this->rendered ?? $this->name . '_rendered');
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
