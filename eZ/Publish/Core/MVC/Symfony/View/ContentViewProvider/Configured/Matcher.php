<?php
/**
 * File containing the Matcher interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured;

use eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Main interface for matchers to be used with View\Provider\Content\Configured.
 */
interface Matcher
{
    /**
     * Registers the matching configuration for the matcher.
     * It's up to the implementor to validate $matchingConfig since it can be anything configured by the end-developer.
     *
     * @param mixed $matchingConfig
     * @return void
     * @throws \InvalidArgumentException Should be thrown if $matchingConfig is not valid.
     */
    public function setMatchingConfig( $matchingConfig );

    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return bool
     */
    public function matchLocation( Location $location );

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @return bool
     */
    public function matchContentInfo( ContentInfo $contentInfo );
}
