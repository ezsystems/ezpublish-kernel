<?php

/**
 * File containing the ContentBasedMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface as ContentBasedMatcherInterface;
use InvalidArgumentException;

/**
 * @deprecated Deprecated since 6.0, will be removed in 6.1. Use the ClassNameMatcherFactory instead.
 */
abstract class ContentBasedMatcherFactory extends AbstractMatcherFactory
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased';

    protected function getMatcher($matcherIdentifier)
    {
        @trigger_error(
            "ContentBasedMatcherFactory is deprecated, and will be removed in ezpublish-kernel 6.1.\n" .
            'Use the ServiceAwareMatcherFactory with the relative namespace as a constructor argument.',
            E_USER_DEPRECATED
        );

        $matcher = parent::getMatcher($matcherIdentifier);
        if (!$matcher instanceof ContentBasedMatcherInterface) {
            throw new InvalidArgumentException(
                'Content based Matcher must implement eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface.'
            );
        }

        return $matcher;
    }
}
