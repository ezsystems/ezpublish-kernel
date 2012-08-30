<?php
/**
 * File containing the UrlAlias Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\SPI\Persistence\Content\UrlAlias;

/**
 * UrlAlias Mapper
 */
class Mapper
{
    /**
     * Creates a UrlAlias object from database row data
     *
     * @param $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function extractUrlAliasFromData( $data )
    {
        $urlAlias = new UrlAlias();

        $urlAlias->id = $data["parent"] . "-" . $data["text_md5"];
        $urlAlias->languageCodes = $data["language_codes"];
        $urlAlias->alwaysAvailable = $data["always_available"];
        $urlAlias->isHistory = !$data["is_original"];
        $urlAlias->isCustom = (boolean)$data["is_alias"];
        $urlAlias->path = $data["path"];
        $urlAlias->forward = $data["forward"];
        $urlAlias->destination = $data["destination"];
        $urlAlias->type = $data["type"];

        return $urlAlias;
    }

    /**
     * Extracts UrlAlias objects from database $rows
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function extractUrlAliasListFromData( array $rows )
    {
        $urlAliases = array();
        foreach ( $rows as $row )
            $urlAliases[] = $this->extractUrlAliasFromData( $row );

        return $urlAliases;
    }
}
