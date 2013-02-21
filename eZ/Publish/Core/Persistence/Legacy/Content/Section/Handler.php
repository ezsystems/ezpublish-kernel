<?php
/**
 * File containing the Section Handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Section;

use eZ\Publish\SPI\Persistence\Content\Section\Handler as BaseSectionHandler;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use RuntimeException;

/**
 * Section Handler
 */
class Handler implements BaseSectionHandler
{
    /**
     * Section Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway
     */
    protected $sectionGateway;

    /**
     * Creates a new Section Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway $sectionGateway
     */
    public function __construct( Gateway $sectionGateway  )
    {
        $this->sectionGateway = $sectionGateway;
    }

    /**
     * Create a new section
     *
     * @param string $name
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function create( $name, $identifier )
    {
        $section = new Section();

        $section->name = $name;
        $section->identifier = $identifier;

        $section->id = $this->sectionGateway->insertSection( $name, $identifier );

        return $section;
    }

    /**
     * Update name and identifier of a section
     *
     * @param mixed $id
     * @param string $name
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function update( $id, $name, $identifier )
    {
        $this->sectionGateway->updateSection( $id, $name, $identifier );

        $section = new Section();
        $section->id = $id;
        $section->name = $name;
        $section->identifier = $identifier;

        return $section;
    }

    /**
     * Get section data
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function load( $id )
    {
        $rows = $this->sectionGateway->loadSectionData( $id );

        if ( empty( $rows ) )
        {
            throw new NotFound( "Section", $id );
        }
        return $this->createSectionFromArray( reset( $rows ) );
    }

    /**
     * Get all section data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll()
    {
        $rows = $this->sectionGateway->loadAllSectionData();
        return $this->createSectionsFromArray( $rows );
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
        $rows = $this->sectionGateway->loadSectionDataByIdentifier( $identifier );

        if ( empty( $rows ) )
        {
            throw new NotFound( "Section", $identifier );
        }
        return $this->createSectionFromArray( reset( $rows ) );
    }

    /**
     * Creates a Section from the given $data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    protected function createSectionFromArray( array $data )
    {
        $section = new Section();

        $section->id = (int)$data['id'];
        $section->name = $data['name'];
        $section->identifier = $data['identifier'];

        return $section;
    }

    /**
     * Creates a Section from the given $data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    protected function createSectionsFromArray( array $data )
    {
        $sections = array();
        foreach ( $data as $sectionData )
        {
            $sections[] = $this->createSectionFromArray( $sectionData );
        }
        return $sections;
    }

    /**
     * Delete a section
     *
     * Might throw an exception if the section is still associated with some
     * content objects. Make sure that no content objects are associated with
     * the section any more *before* calling this method.
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $contentCount = $this->sectionGateway->countContentObjectsInSection( $id );

        if ( $contentCount > 0 )
        {
            throw new RuntimeException(
                "Section with ID '{$id}' still has content assigned."
            );
        }
        $this->sectionGateway->deleteSection( $id );
    }

    /**
     * Assigns section to single content object
     *
     * @param mixed $sectionId
     * @param mixed $contentId
     */
    public function assign( $sectionId, $contentId )
    {
        $this->sectionGateway->assignSectionToContent( $sectionId, $contentId );
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
        return $this->sectionGateway->countContentObjectsInSection( $sectionId );
    }
}
