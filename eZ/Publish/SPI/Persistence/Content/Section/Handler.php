<?php
/**
 * File containing the Section Handler interface
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Section;

use eZ\Publish\SPI\Persistence\Content\Section\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Section\UpdateStruct;

/**
 */
interface Handler
{
    /**
     * Create a new section.
     *
     * The caller ensures that the identifier does not exist
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Section\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function create( CreateStruct $createStruct );

    /**
     * Update names, description or identifier of a section
     *
     * The caller ensures that the new identifier does not exist
     *
     * @param mixed $id
     * @param \eZ\Publish\SPI\Persistence\Content\Section\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function update( $id, UpdateStruct $updateStruct );

    /**
     * Get section data
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function load( $id );

    /**
     * Get all section data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll();

    /**
     * Get section data by identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function loadByIdentifier( $identifier );

    /**
     * Delete a section
     *
     * The caller ensures that the section exists and no content objects are assigned
     *
     * @param mixed $id
     */
    public function delete( $id );

    /**
     * Assigns section to single content object
     *
     * @param mixed $sectionId
     * @param mixed $contentId
     */
    public function assign( $sectionId, $contentId );

    /**
     * Number of content assignments a Section has
     *
     * @param mixed $sectionId
     *
     * @return int
     */
    public function assignmentsCount( $sectionId );
}
