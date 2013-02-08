<?php
/**
 * File containing the MatcherBuilderInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

interface MatcherBuilderInterface
{
    /**
     * Builds siteaccess matcher.
     *
     * @param string $matcherIdentifier "Identifier" of the matcher to build (i.e. its FQ class name).
     * @param mixed $matchingConfiguration Configuration to pass to the matcher. Can be anything the matcher supports.
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request The request to match against.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher
     *
     * @throws \RuntimeException
     */
    public function buildMatcher( $matcherIdentifier, $matchingConfiguration, SimplifiedRequest $request );
}
