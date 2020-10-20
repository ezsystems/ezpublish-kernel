<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\Templating;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

abstract class BaseRenderStrategy implements RenderStrategy
{
    /** @var \Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface[] */
    protected $fragmentRenderers;

    /** @var string */
    protected $defaultRenderer;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    protected $siteAccess;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    protected $requestStack;

    public function __construct(
        iterable $fragmentRenderers,
        string $defaultRenderer,
        SiteAccess $siteAccess,
        RequestStack $requestStack
    ) {
        foreach ($fragmentRenderers as $fragmentRenderer) {
            $this->fragmentRenderers[$fragmentRenderer->getName()] = $fragmentRenderer;
        }

        $this->defaultRenderer = $defaultRenderer;
        $this->siteAccess = $siteAccess;
        $this->requestStack = $requestStack;
    }

    protected function getFragmentRenderer(string $name): FragmentRendererInterface
    {
        if (empty($this->fragmentRenderers[$name])) {
            throw new InvalidArgumentException('method', sprintf(
                'Fragment renderer "%s" does not exist.', $name
            ));
        }

        return $this->fragmentRenderers[$name];
    }
}
