<?php
/**
 * File containing the Section Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway,
    eZ\Publish\SPI\Persistence\Content\UrlWildcard,
    ezcDbException,
    PDOException;

/**
 * UrlAlias Handler
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Inserts the given UrlWildcard
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $urlWildcard
     *
     * @return mixed
     */
    public function insertUrlWildcard( UrlWildcard $urlWildcard )
    {
        try
        {
            return $this->innerGateway->insertUrlWildcard( $urlWildcard );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the UrlWildcard with given $id
     *
     * @param mixed $id
     *
     * @return void
     */
    public function deleteUrlWildcard( $id )
    {
        try
        {
            return $this->innerGateway->deleteUrlWildcard( $id );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     *
     *
     * @param mixed $parentId
     *
     * @return array
     */
    public function loadUrlWildcardData( $parentId )
    {
        try
        {
            return $this->innerGateway->loadUrlWildcardData( $parentId );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads an array with data about UrlWildcards (paged)
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    public function loadUrlWildcardsData( $offset = 0, $limit = -1 )
    {
        try
        {
            return $this->innerGateway->loadUrlWildcardsData( $offset, $limit );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
