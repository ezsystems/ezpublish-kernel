<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\MVC\Templating\BaseRenderStrategy;
use eZ\Publish\SPI\MVC\Templating\RenderStrategy;
use Symfony\Component\HttpFoundation\Request;

final class RenderContentStrategy extends BaseRenderStrategy implements RenderStrategy
{
    private const DEFAULT_VIEW_TYPE = 'embed';

    public function supports(ValueObject $valueObject): bool
    {
        return $valueObject instanceof Content;
    }

    public function render(ValueObject $valueObject, RenderOptions $options): string
    {
        if (!$this->supports($valueObject)) {
            throw new InvalidArgumentException(
                'valueObject',
                'Must be a type of ' . Content::class
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $valueObject;

        $currentRequest = $this->requestStack->getCurrentRequest();
        $surrogateCapability = $currentRequest->headers->get('Surrogate-Capability');

        $request = new Request();
        $request->headers->set('siteaccess', $this->siteAccess->name);
        $request->headers->set('Surrogate-Capability', $surrogateCapability);

        $request->attributes->add([
            '_route' => '_ez_content_view',
            '_controller' => 'ez_content::viewAction',
            'siteaccess' => $this->siteAccess,
            'contentId' => $content->id,
            'viewType' => $options->get('viewType', self::DEFAULT_VIEW_TYPE),
        ]);

        $renderMethod = $this->getRenderMethod($options, $content);

        return $renderMethod->render($request);
    }
}
