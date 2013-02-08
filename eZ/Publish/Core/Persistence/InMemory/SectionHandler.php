<?php
/**
 * File containing the SectionHandler implementation
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandlerInterface;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use LogicException;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
 */
class SectionHandler implements SectionHandlerInterface
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
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function create( $name, $identifier )
    {
        return $this->backend->create(
            'Content\\Section',
            array(
                'name' => $name,
                'identifier' => $identifier
            )
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function update( $id, $name, $identifier )
    {
        $this->backend->update(
            'Content\\Section',
            $id,
            array(
                'id' => $id,
                'name' => $name,
                'identifier' => $identifier
            )
        );
        return $this->load( $id );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function load( $id )
    {
        return $this->backend->load( 'Content\\Section', $id );
    }

    /**
     * Get all section data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll()
    {
        return $this->backend->find( 'Content\\Section' );
    }

    /**
     * Get section data by identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function loadByIdentifier( $identifier )
    {
        $list = $this->backend->find( 'Content\\Section', array( 'identifier' => $identifier ) );
        if ( empty( $list ) )
            throw new NotFound( 'Section', $identifier );
        if ( isset( $list[1] ) )
            throw new LogicException( 'Several Sections with same identifier' );

        return $list[0];
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function delete( $id )
    {
        $this->backend->delete( 'Content\\Section', $id );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function assign( $sectionId, $contentId )
    {
        $this->backend->update(
            'Content\\ContentInfo',
            $contentId,
            array(
                'sectionId' => $sectionId,
            ),
            true
        );
    }

    /**
     * Number of content assignments a Section has
     *
     * @param mixed $sectionId
     *
     * @return int
     */
    public function assignmentsCount( $sectionId )
    {
        return $this->backend->count( 'Content\\ContentInfo', array( 'sectionId' => $sectionId ) );
    }
}
