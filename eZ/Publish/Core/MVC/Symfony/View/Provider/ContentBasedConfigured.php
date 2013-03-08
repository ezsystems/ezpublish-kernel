<?php
/**
 * File containing the ContentBasedConfigured view provider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

abstract class ContentBasedConfigured extends Configured
{
    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *                                  If it begins with a '\' it means it's a FQ class name, otherwise it is relative to
     *                                  eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher namespace.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $matcherIdentifier[0] !== '\\' )
            $matcherIdentifier = "eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher\\$matcherIdentifier";

        return new $matcherIdentifier();
    }
}
