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
     * Creates a UrlAlias object from database data
     *
     * @param $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function extractUrlAliasFromRow( $data )
    {
        $urlAlias = new UrlAlias();

        $urlAlias->id = $data["parent"] . "-" . $data["text_md5"];
        $urlAlias->languageCodes = $data["language_codes"];
        $urlAlias->alwaysAvailable = $data["always_available"];
        $urlAlias->isHistory = !$data["is_original"];
        $urlAlias->isCustom = (boolean)$data["is_alias"];
        $urlAlias->path = $data["path"];
        $urlAlias->forward = $data["forward"];
        $urlAlias->destination = $data["destination"] ?: $this->actionToDestination( $data["action"] );
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
    public function extractUrlAliasListFromRows( array $rows )
    {
        $urlAliases = array();
        foreach ( $rows as $row )
            $urlAliases[] = $this->extractUrlAliasFromRow( $row );

        return $urlAliases;
    }

    /**
     *
     *
     * @param $action
     *
     * @return array
     */
    protected function actionToDestination( $action )
    {
        $destination = false;

        if ( preg_match( "#^([a-zA-Z0-9_]+):(.+)?$#", $action, $matches ) )
        {
            $typeString = $matches[1];
            $arguments = isset( $matches[2] ) ? $matches[2] : false;

            switch ( $typeString )
            {
                case "eznode":
                    $destination = is_numeric( $arguments ) ? $arguments : false;
                    break;

                case "module":
                    $destination = $arguments;
                    break;

                case "nop":
                    $destination = "/";
                    break;
            }
        }

        return $destination;
    }
}
