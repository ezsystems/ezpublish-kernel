<?php
/**
 * File containing the eZ\Publish\API\Repository\URLWildcardService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

/**
 * URLAlias service
 *
 * @example Examples/urlalias.php
 *
 * @package eZ\Publish\API\Repository
 */
interface URLWildcardService
{
     /**
     * creates a new url wildcard
     * 
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $foreward
     * 
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function create($sourceUrl, $destinationUrl, $foreward = false);
    
    /**
     * 
     * removes an url wildcard
     * 
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard
     */
    public function remove($urlWildcard);
    
    /**
     * 
     * loads a url wild card
     * 
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     * 
     * @param $id
     * 
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function load($id);
    
    /**
     * loads all url wild card (paged)
     * 
     * @param $offset
     * @param $limit
     * 
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard[]
     */
    public function loadAll($offset = 0, $limit = -1);
    
    /**
     * translates an url to an existing uri resource or url alias based on the source/destination patterns of the url wildcard.
     * this method runs also configured url translations and filter
     * 
     * @param $url
     * 
     * @return mixed either an URLAlias or a URLWildcardTranslationResult
     */
    public function translate($url);
    
}