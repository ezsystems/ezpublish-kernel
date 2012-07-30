<?php
/**
 * File containing the UrlWildcard Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * The UrlWildcard Handler interface provides nice urls with wildcards management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
interface Handler
{
    /**
     * creates a new url wildcard
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $forward
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function create( $sourceUrl, $destinationUrl, $forward = false );

    /**
     *
     * removes an url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     */
    public function remove( $id );

    /**
     *
     * loads a url wild card
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function load( $id );

    /**
     * loads all url wild card (paged)
     *
     * @param $offset
     * @param $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function loadAll( $offset = 0, $limit = -1 );



}
