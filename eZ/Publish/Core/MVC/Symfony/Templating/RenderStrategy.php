<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\MVC\Templating\RenderStrategy as SPIRenderStrategy;

final class RenderStrategy implements SPIRenderStrategy
{
    /** @var \eZ\Publish\SPI\MVC\Templating\RenderStrategy[] */
    private $strategies;

    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function supports(ValueObject $valueObject): bool
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($valueObject)) {
                return true;
            }
        }

        return false;
    }

    public function render(ValueObject $valueObject, RenderOptions $options): string
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($valueObject)) {
                return $strategy->render($valueObject, $options);
            }
        }

        throw new InvalidArgumentException('valueObject', sprintf(
            "Method '%s' is not supported for %s.", $options->get('method'), get_class($valueObject)
        ));
    }
}
