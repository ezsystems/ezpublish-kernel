<?php

/**
 * File containing the BlockMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\Matcher\Block\MatcherInterface as BlockMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface as BaseMatcherInterface;
use InvalidArgumentException;

class BlockMatcherFactory extends AbstractMatcherFactory
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\Block';

    protected function getMatcher($matcherIdentifier)
    {
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
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\Block\MatcherInterface|\eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface $matcher
     * @param ValueObject $valueObject
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    protected function doMatch(BaseMatcherInterface $matcher, ValueObject $valueObject)
    {
        if (!$valueObject instanceof Block) {
            throw new InvalidArgumentException('Value object must be a valid Block instance');
        }

        return $matcher->matchBlock($valueObject);
    }
}
