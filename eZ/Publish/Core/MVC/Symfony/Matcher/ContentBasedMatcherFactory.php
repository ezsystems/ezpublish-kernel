<?php
/**
 * File containing the ContentBasedMacherFactory class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface;
use InvalidArgumentException;

abstract class ContentBasedMatcherFactory extends AbstractMatcherFactory
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased';

    protected function getMatcher( $matcherIdentifier )
    {
        $matcher = parent::getMatcher( $matcherIdentifier );
        if ( !$matcher instanceof MatcherInterface )
        {
            throw new InvalidArgumentException(
                'Content based Matcher must implement eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface.'
            );
        }

        return $matcher;
    }
}