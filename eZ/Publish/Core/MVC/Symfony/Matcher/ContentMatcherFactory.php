<?php

/**
 * File containing the ContentMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use InvalidArgumentException;

/**
 * @deprecated Deprecated since 6.0, will be removed in 6.1. Use the ClassNameMatcherFactory instead.
 */
class ContentMatcherFactory extends ContentBasedMatcherFactory
{
    /**
     * Checks if $valueObject matches $matcher rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface $matcher
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return bool
     * @internal param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     */
    protected function doMatch(MatcherInterface $matcher, View $view)
    {
        if (!$view instanceof ContentValueView) {
            throw new InvalidArgumentException('View must be a ContentValueView instance');
        }

        return $matcher->match($view);
    }
}
