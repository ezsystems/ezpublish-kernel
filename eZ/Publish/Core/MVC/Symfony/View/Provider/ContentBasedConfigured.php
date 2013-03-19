<?php
/**
 * File containing the ContentBasedConfigured view provider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;
use InvalidArgumentException;

abstract class ContentBasedConfigured extends Configured
{
    const MATCHER_RELATIVE_NAMESPACE = 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher';

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *                                  If it begins with a '\' it means it's a FQ class name, otherwise it is relative to
     *                                  eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher namespace.
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        $matcher = parent::getMatcher( $matcherIdentifier );
        if ( !$matcher instanceof Matcher )
        {
            throw new InvalidArgumentException(
                'Matcher for ContentViewProvider\\Configured must implement eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher interface.'
            );
        }

        return $matcher;
    }
}
