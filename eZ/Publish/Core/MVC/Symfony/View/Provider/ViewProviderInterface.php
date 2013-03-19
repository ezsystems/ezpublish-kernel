<?php
/**
 * File containing the ViewProviderInterface class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Main interface for view providers.
 */
interface ViewProviderInterface
{
    /**
     * Checks if $valueObject matches the $matcher's rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher $matcher
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     *
     * @throws \InvalidArgumentException If $valueObject is not of expected sub-type.
     *
     * @return bool
     */
    public function match( ViewProviderMatcher $matcher, ValueObject $valueObject );
}
