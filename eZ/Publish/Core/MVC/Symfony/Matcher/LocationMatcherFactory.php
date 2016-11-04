<?php

/**
 * File containing the LocationMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use InvalidArgumentException;

/**
 * @deprecated since 6.0 location view in general is deprecated. Use content view instead.
 */
class LocationMatcherFactory extends ContentBasedMatcherFactory
{
    /**
     * Checks if $valueObject matches $matcher rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface $matcher
     * @param ValueObject $valueObject
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function doMatch(MatcherInterface $matcher, View $view)
    {
        @trigger_error(
            "LocationMatcherFactory is deprecated, and will be removed in ezpublish-kernel 6.1.\n" .
            'Use the ServiceAwareMatcherFactory with the relative namespace as a constructor argument.',
            E_USER_DEPRECATED
        );

        if (!$view instanceof LocationValueView) {
            throw new InvalidArgumentException('Value object must be a valid Location instance');
        }

        return $matcher->matchLocation($view);
    }
}
