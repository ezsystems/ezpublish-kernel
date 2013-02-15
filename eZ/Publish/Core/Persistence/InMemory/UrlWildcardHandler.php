<?php
/**
 * File containing the UrlWildcard Handler implementation
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as UrlWildcardHandlerInterface;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * @see eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
 */
class UrlWildcardHandler implements UrlWildcardHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
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
        return $this->backend->create(
            'Content\\UrlWildcard',
            array(
                'sourceUrl' => $sourceUrl,
                'destinationUrl' => $destinationUrl,
                'forward' => (bool)$forward
            )
        );
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
        $this->backend->delete( 'Content\\UrlWildcard', $id );
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
        return $this->backend->load( 'Content\\UrlWildcard', $id );
    }

    /**
     * Loads all url wild card (paged)
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function loadAll( $offset = 0, $limit = -1 )
    {
        $list = $this->backend->find( 'Content\\UrlWildcard' );

        if ( empty( $list ) || ( $offset === 0 && $limit === -1 ) )
            return $list;

        return array_slice( $list, $offset, ( $limit === -1 ? null : $limit ) );
    }
}
