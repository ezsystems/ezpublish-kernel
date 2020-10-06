<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\Templating;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseRenderStrategy implements RenderStrategy
{
    /** @var string */
    protected $defaultMethod;

    /** @var \eZ\Publish\SPI\MVC\Templating\RenderMethod[] */
    protected $renderMethods = [];

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    protected $siteAccess;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    protected $requestStack;

    public function __construct(
        iterable $renderMethods,
        string $defaultMethod,
        SiteAccess $siteAccess,
        RequestStack $requestStack
    ) {
        $this->defaultMethod = $defaultMethod;
        $this->siteAccess = $siteAccess;

        foreach ($renderMethods as $renderMethod) {
            $this->renderMethods[$renderMethod->getIdentifier()] = $renderMethod;
        }
        $this->requestStack = $requestStack;
    }

    protected function getRenderMethod(RenderOptions $options, ValueObject $valueObject): RenderMethod
    {
        $method = $options->get('method', $this->defaultMethod);

        if (empty($this->renderMethods[$method])) {
            throw new InvalidArgumentException('method', sprintf(
                "Method '%s' is not supported for %s.", $method, get_class($valueObject)
            ));
        }

        return $this->renderMethods[$method];
    }
}
