<?php
/**
 * File containing the UrlWildcard Handler
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as BaseUrlWildcardHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * The UrlWildcard Handler provides nice urls with wildcards management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
class Handler implements BaseUrlWildcardHandler
{
    /**
     * UrlWildcard Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway
     */
    protected $gateway;

    /**
     * UrlWildcard Mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper
     */
    protected $mapper;

    /**
     * Creates a new UrlWildcard Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper $mapper
     */
    public function __construct( Gateway $gateway, Mapper $mapper )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Creates a new url wildcard
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $forward
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function create( $sourceUrl, $destinationUrl, $forward = false )
    {
        $urlWildcard = $this->mapper->createUrlWildcard(
            $sourceUrl,
            $destinationUrl,
            $forward
        );

        $urlWildcard->id = $this->gateway->insertUrlWildcard( $urlWildcard );

        return $urlWildcard;
    }

    /**
     * removes an url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     */
    public function remove( $id )
    {
        $this->gateway->deleteUrlWildcard( $id );
    }

    /**
     * Loads a url wild card
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function load( $id )
    {
        $row = $this->gateway->loadUrlWildcardData( $id );

        if ( empty( $row ) )
        {
            throw new NotFoundException( "UrlWildcard", $id );
        }

        return $this->mapper->extractUrlWildcardFromRow( $row );
    }

    /**
     * Loads all url wild card (paged)
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function loadAll( $offset = 0, $limit = -1 )
    {
        return $this->mapper->extractUrlWildcardsFromRows(
            $this->gateway->loadUrlWildcardsData( $offset, $limit )
        );
    }
}
