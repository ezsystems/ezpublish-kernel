<?php
/**
 * File containing the UrlAlias matcher class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\Content\ContentInfo;

class UrlAlias extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return bool
     */
    public function matchLocation( Location $location )
    {
        $locationUrls = $this->repository->getURLAliasService()->listLocationAliases( $location, true );
        foreach ( $this->values as $pattern => $val )
        {
            foreach ( $locationUrls as $urlAlias )
            {
                if ( strpos( $urlAlias->path, $pattern ) === 0 )
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Not supported since UrlAlias is meaningful for location objects only.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @throws \RuntimeException
     * @return bool
     */
    public function matchContentInfo( ContentInfo $contentInfo )
    {
        throw new \RuntimeException( 'matchContentInfo() is not supported by UrlAlias matcher' );
    }

    public function setMatchingConfig( $matchingConfig )
    {
        if ( !is_array( $matchingConfig ) )
        {
            $matchingConfig = array( $matchingConfig );
        }

        array_walk(
            $matchingConfig,
            function ( &$item, $key ) {
                $item = trim( $item, '/ ' );
            }
        );

        parent::setMatchingConfig( $matchingConfig );
    }
}
