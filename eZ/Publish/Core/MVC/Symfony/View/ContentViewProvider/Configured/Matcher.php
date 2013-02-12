<?php
/**
 * File containing the Matcher interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

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
     *
     * @throws \InvalidArgumentException Should be thrown if $matchingConfig is not valid.
     *
     * @return void
     */
    public function setMatchingConfig( $matchingConfig );

    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return boolean
     */
    public function matchLocation( Location $location );

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return boolean
     */
    public function matchContentInfo( ContentInfo $contentInfo );
}
