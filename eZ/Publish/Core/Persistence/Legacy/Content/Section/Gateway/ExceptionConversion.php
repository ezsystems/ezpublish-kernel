<?php
/**
 * File containing the Section Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;

/**
 * Section Handler
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Inserts a new section with $name and $identifier
     *
     * @param string $name
     * @param string $identifier
     *
     * @return int The ID of the new section
     */
    public function insertSection( $name, $identifier )
    {
        try
        {
            return $this->innerGateway->insertSection( $name, $identifier );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates section with $id to have $name and $identifier
     *
     * @param int $id
     * @param string $name
     * @param string $identifier
     *
     * @return void
     */
    public function updateSection( $id, $name, $identifier )
    {
        try
        {
            return $this->innerGateway->updateSection( $id, $name, $identifier );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for section with $id
     *
     * @param int $id
     *
     * @return string[][]
     */
    public function loadSectionData( $id )
    {
        try
        {
            return $this->innerGateway->loadSectionData( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for all sections
     *
     * @return string[][]
     */
    public function loadAllSectionData()
    {
        try
        {
            return $this->innerGateway->loadAllSectionData();
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for section with $identifier
     *
     * @param string $identifier
     *
     * @return string[][]
     */
    public function loadSectionDataByIdentifier( $identifier )
    {
        try
        {
            return $this->innerGateway->loadSectionDataByIdentifier( $identifier );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Counts the number of content objects assigned to section with $id
     *
     * @param int $id
     *
     * @return int
     */
    public function countContentObjectsInSection( $id )
    {
        try
        {
            return $this->innerGateway->countContentObjectsInSection( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the Section with $id
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteSection( $id )
    {
        try
        {
            return $this->innerGateway->deleteSection( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts the assignment of $contentId to $sectionId
     *
     * @param int $sectionId
     * @param int $contentId
     *
     * @return void
     */
    public function assignSectionToContent( $sectionId, $contentId )
    {
        try
        {
            return $this->innerGateway->assignSectionToContent( $sectionId, $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
