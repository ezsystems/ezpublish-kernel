<?php

/**
 * File containing the ContentBasedMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface as ContentBasedMatcherInterface;
use InvalidArgumentException;

abstract class ContentBasedMatcherFactory extends AbstractMatcherFactory
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased';

    protected function getMatcher($matcherIdentifier)
    {
        $matcher = parent::getMatcher($matcherIdentifier);
        if (!$matcher instanceof ContentBasedMatcherInterface) {
            throw new InvalidArgumentException(
                'Content based Matcher must implement eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface.'
            );
        }

        return $matcher;
    }
}
