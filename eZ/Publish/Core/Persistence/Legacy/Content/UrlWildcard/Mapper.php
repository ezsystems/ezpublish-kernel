<?php
/**
 * File containing the UrlWildcard Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * UrlWildcard Mapper
 */
class Mapper
{
    /**
     * Creates a UrlWildcard object from given parameters
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $forward
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function createUrlWildcard( $sourceUrl, $destinationUrl, $forward )
    {
        $urlWildcard = new UrlWildcard();

        $urlWildcard->destinationUrl = $destinationUrl;
        $urlWildcard->sourceUrl = $sourceUrl;
        $urlWildcard->forward = $forward;

        return $urlWildcard;
    }

    /**
     * Extracts UrlWildcard objects from database $rows
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function extractUrlWildcardsFromRows( array $rows )
    {
        $urlWildcards = array();

        foreach ( $rows as $row )
        {
            $urlWildcard = new UrlWildcard();

            $urlWildcard->destinationUrl = $row["locale"];
            $urlWildcard->id = (int)$row["id"];
            $urlWildcard->sourceUrl = $row["name"];
            $urlWildcard->forward = (int)$row["type"] === 1 ? true : false;

            $urlWildcards[] = $urlWildcard;
        }

        return $urlWildcards;
    }
}
