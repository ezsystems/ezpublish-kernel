<?php

/**
 * File containing the BlockMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Matcher\Block\MatcherInterface as BlockMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface as BaseMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\View\View;
use InvalidArgumentException;

/**
 * @deprecated Deprecated since 6.0, will be removed in 6.1. Use the AbstractMatcherFactory instead.
 */
class BlockMatcherFactory extends AbstractMatcherFactory
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\Block';

    protected function getMatcher($matcherIdentifier)
    {
        @trigger_error(
            "BlockMatcherFactory is deprecated, and will be removed in ezpublish-kernel 6.1.\n" .
            'Use the ServiceAwareMatcherFactory with the relative namespace as a constructor argument instead.',
            E_USER_DEPRECATED
        );

        $matcher = parent::getMatcher($matcherIdentifier);
        if (!$matcher instanceof BlockMatcherInterface) {
            throw new InvalidArgumentException(
                'Matcher for Blocks must implement eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\Block\\MatcherInterface.'
            );
        }

        return $matcher;
    }

    /**
     * Checks if $valueObject matches $matcher rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface $matcher
     * @param ValueObject $valueObject
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    protected function doMatch(BaseMatcherInterface $matcher, View $view)
    {
        return $matcher->match($view);
    }
}
