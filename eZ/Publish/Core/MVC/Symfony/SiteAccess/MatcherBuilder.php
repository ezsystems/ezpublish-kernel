<?php
/**
 * File containing the Siteaccess MatcherBuilder class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

/**
 * Siteaccess matcher builder, based on class names.
 */
class MatcherBuilder implements MatcherBuilderInterface
{
    /**
     * Builds siteaccess matcher.
     * In the siteaccess configuration, if the matcher class begins with a "\" (FQ class name), it will be used as is, passing the matching configuration in the constructor.
     * Otherwise, given matching class will be relative to eZ\Publish\Core\MVC\Symfony\SiteAccess namespace.
     *
     * @param string $matcherIdentifier "Identifier" of the matcher to build (i.e. its FQ class name).
     * @param mixed $matchingConfiguration Configuration to pass to the matcher. Can be anything the matcher supports.
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request The request to match against.
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher
     */
    public function buildMatcher( $matcherIdentifier, $matchingConfiguration, SimplifiedRequest $request )
    {
        // If class begins with a '\' it means it's a FQ class name,
        // otherwise it is relative to this namespace.
        if ( $matcherIdentifier[0] !== '\\' )
            $matcherIdentifier = __NAMESPACE__ . "\\Matcher\\$matcherIdentifier";

        $matcher = new $matcherIdentifier( $matchingConfiguration );
        $matcher->setRequest( $request );

        return $matcher;
    }
}
