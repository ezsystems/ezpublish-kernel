<?php
/**
 * File containing the UrlAliasHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Interfaces;

/**
 * The UrlAliasHandler interface provides nice urls management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
interface UrlAliasHandler
{
    /**
      * Create a (nice) url alias, $path pointing to $locationId, in $languageName.
      *
      * $alwaysAvailable controls whether the url alias is accessible in all
      * languages.
      *
      * @param string $path
      * @param string $locationId
      * @param string $languageName
      * @param bool $alwaysAvailable
      */
     public function storeUrlAliasPath( $path, $locationId, $languageName = null, $alwaysAvailable = false );

     /**
      * Create a user chosen $alias pointing to $locationId in $languageName.
      *
      * If $languageName is null the $alias is created in the system's default
      * language. $alwaysAvailable makes the alias available in all languages.
      *
      * @param string $alias
      * @param int $locationId
      * @param boolean $forwarding
      * @param string $languageName
      * @param bool $alwaysAvailable
      * @return boolean
      */
     public function createCustomUrlAlias( $alias, $locationId, $forwarding = false, $languageName = null, $alwaysAvailable = false );

     /**
      * Create a history url entry.
      *
      * History url entries constitutes a log of earlier url aliases to a location,
      * and allows old urls to hit the location, even if the current url is a
      * different one.
      *
      * @param $historicUrl
      * @param $locationId
      * @return boolean
      */
     public function createUrlHistoryEntry( $historicUrl, $locationId );

     /**
      * List of url entries of $urlType, pointing to $locationId.
      *
      * @param $locationId
      * @param $urlType
      * @return mixed
      */
     public function listUrlsForLocation( $locationId, $urlType );

     /**
      * Removes urls pointing to $locationId, identified by the element in $urlIdentifier.
      *
      * @param $locationId
      * @param array $urlIdentifier
      * @return boolean
      */
     public function removeUrlsForLocation( $locationId, array $urlIdentifier );

     /**
      * Returns the full url alias to $locationId from /.
      *
      * @param $locationId
      * @param $languageCode
      * @return string
      */
     public function getPath( $locationId, $languageCode );

}
