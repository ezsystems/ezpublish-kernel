<?php
/**
 * File containing the Section ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Section\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Section\Gateway,
    ezp\Persistence\Content\Section,
    ezp\Persistence\Storage\Legacy\EzcDbHandler;

/**
 * Section Handler
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @param ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new EzcDatabase Section Gateway
     *
     * @param ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     */
    public function __construct ( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Inserts a new section with $name and $identifier
     *
     * @param string $name
     * @param string $identifier
     * @return int The ID of the new section
     */
    public function insertSection( $name, $identifier )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Updates section with $id to have $name and $identifier
     *
     * @param int $id
     * @param string $name
     * @param string $identifier
     * @return void
     */
    public function updateSection( $id, $name, $identifier )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Loads data for section with $id
     *
     * @param int $id
     * @return string[][]
     */
    public function loadSectionData( $id )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Counts the number of content objects assigned to section with $id
     *
     * @param int $id
     * @return int
     */
    public function countContentObjectsInSection( $id )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Deletes the Section with $id
     *
     * @param int $id
     * @return void
     */
    public function deleteSection( $id )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Inserts the assignment of $contentId to $sectionId
     *
     * @param int $sectionId
     * @param int $contentId
     * @return void
     */
    public function assignSectionToContent( $sectionId, $contentId )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
