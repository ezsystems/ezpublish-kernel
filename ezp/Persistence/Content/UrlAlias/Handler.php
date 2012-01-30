<?php
/**
 * File containing the UrlAlias Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\UrlAlias;

/**
 * The UrlAlias Handler interface provides nice urls management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
interface Handler
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
      * @param boolean $alwaysAvailable
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
      * @param boolean $alwaysAvailable
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
      * For best performance, a full path string should be used, and then the
      * abstraction of eZURLAliasML::fetchPathByActionList(…) is preferred to be used.
      *
      * Secondly the recursive eZURLAliasML::getPath(…) shouold be used.
      * This is also required if a path is to be fetched in another $language,
      * than what is currently the most prioritized language in the context of eZURLAliasML.
      *
      * @param $locationId
      * @param $language
      * @return string
      */
     public function getPath( $locationId, $language );

    /**
     * Runs filters which are defined to be run on url aliases in the legacy engine,
     * and returns the modified $urlText for the path element representing $locationId.
     *
     * See ezpublish/doc/features/3.10/multilingual_support_for_urlalias.txt,
     * "Filtering of alias text" for details, on the Legacy implementation this should connect to.
     *
     * Relevant settings:
     * site.ini.[URLTranslator].FilterClasses
     *
     * This method is an abstraction for the functionality of eZURLAliasFilter::processFilters(…)
     *
     * @abstract
     * @param string $urlText
     * @param mixed $locationId
     * @param string $language
     * @return string
     */
    public function runUrlFilters( $urlText, $locationId, $language );

    /**
     * Returns $urlText transformed according to the selected URL transformation settings.
     *
     * Relevant settings:
     * site.ini.[URLTranslator].TransformationGroup
     *
     * This method is an abstraction of the functionality of eZURLAliasML::convertToAlias(…)
     *
     * @abstract
     * @param string $urlText
     * @param string $fallBackValue
     * @return string
     */
    public function convertToUrlAlias( $urlText, $fallBackValue );

    /**
     * Converts $urlText to a unique value for the placement of Location, $locationId.
     *
     * If the url being created for Location $locationId has moved,
     * $locationHasMoved should be set to true, as name conflicts needs to be
     * checked in the new destination.
     *
     * This method represents an abstraction of the functionality of eZContentObjectTreeNode::adjustPathElement(…)
     *
     * @abstract
     * @param string $urlText
     * @param int $locationId
     * @param boolean $locationHasMoved
     * @return string
     */
    public function adjustToUniqueUrlText( $urlText, $locationId, $locationHasMoved = false );
}
