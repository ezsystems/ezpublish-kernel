<?php
/**
 * File containing the UrlWildcard Mapper class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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

        $urlWildcard->destinationUrl = $this->cleanUrl( $destinationUrl );
        $urlWildcard->sourceUrl = $this->cleanUrl( $sourceUrl );
        $urlWildcard->forward = $forward;

        return $urlWildcard;
    }

    /**
     * Extracts UrlWildcard object from given database $row
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function extractUrlWildcardFromRow( array $row )
    {
        $urlWildcard = new UrlWildcard();

        $urlWildcard->id = (int)$row["id"];
        $urlWildcard->destinationUrl = $this->cleanUrl( $row["destination_url"] );
        $urlWildcard->sourceUrl = $this->cleanUrl( $row["source_url"] );
        $urlWildcard->forward = (int)$row["type"] === 1 ? true : false;

        return $urlWildcard;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function cleanUrl( $url )
    {
        return "/" . trim( $url, "/ " );
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
            $urlWildcards[] = $this->extractUrlWildcardFromRow( $row );
        }

        return $urlWildcards;
    }
}
