<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\MVC\Templating\BaseRenderStrategy;
use eZ\Publish\SPI\MVC\Templating\RenderStrategy;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

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
                'Must be a type of ' . Location::class
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $valueObject;

        $currentRequest = $this->requestStack->getCurrentRequest();
        $controllerReference = new ControllerReference('ez_content::viewAction', [
            'contentId' => $content->id,
            'viewType' => $options->get('viewType', self::DEFAULT_VIEW_TYPE),
        ]);

        $renderer = $this->getFragmentRenderer($options->get('method', $this->defaultRenderer));

        return $renderer->render($controllerReference, $currentRequest)->getContent();
    }
}
