<?php
/**
 * File containing the Section Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Section;

/**
 */
interface Handler
{
    /**
     * Creat a new section
     *
     * @param string $name
     * @param string $identifier
     * @return \ezp\Persistence\Content\Section
     * @todo Should validate that $identifier is unique??
     * @todo What about translatable $name?
     */
    public function create( $name, $identifier );

    /**
     * Update name and identifier of a section
     *
     * @param mixed $id
     * @param string $name
     * @param string $identifier
     * @return \ezp\Persistence\Content\Section
     */
    public function update( $id, $name, $identifier );

    /**
     * Get section data
     *
     * @param mixed $id
     * @return \ezp\Persistence\Content\Section
     * @throws \ezp\Base\Exception\NotFound If section is not found
     */
    public function load( $id );

    /**
     * Get all section data
     *
     * @return \ezp\Persistence\Content\Section[]
     */
    public function loadAll();

    /**
     * Get section data by identifier
     *
     * @param string $identifier
     * @return \ezp\Persistence\Content\Section
     * @throws \ezp\Base\Exception\NotFound If section is not found
     */
    public function loadByIdentifier( $identifier );

    /**
     * Delete a section
     *
     * Might throw an exception if the section is still associated with some
     * content objects. Make sure that no content objects are associated with
     * the section any more *before* calling this method.
     *
     * @param mixed $id
     */
    public function delete( $id );

    /**
     * Assign section to single content object
     *
     * @param mixed $sectionId
     * @param mixed $contentId
     */
    public function assign( $sectionId, $contentId );

    /**
     * Number of content assignments a Section has
     *
     * @param mixed $sectionId
     * @return int
     */
    public function assignmentsCount( $sectionId );
}
?>
