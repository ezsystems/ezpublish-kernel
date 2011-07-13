<?php
/**
 * File containing the ezp\Content\Services\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Services;
use ezp\Base\Exception\Validation,
    ezp\Base\AbstractService,
    ezp\Content\Content,
    ezp\Content\Section;

/**
 * Section service, used for section operations
 *
 */
class Section extends AbstractService
{
    /**
     * Creates the a new Section in the content repository
     *
     * @param Section $section
     * @return Section The newly create section
     * @throws Exception\Validation If a validation problem has been found for $section
     */
    public function create( Section $section )
    {
    }

    /**
     * Updates $section in the content repository
     *
     * @param Section $section
     * @return Section
     * @throws Exception\Validation If a validation problem has been found for $section
     */
    public function update( Section $section )
    {
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @param int $sectionId
     * @return Section
     * @throws Exception\NotFound if section could not be found
     */
    public function load( $sectionId )
    {
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @param string $sectionIdentifier
     * @return Section
     * @throws Exception\NotFound if section could not be found
     */
    public function loadByIdentifier( $sectionIdentifier )
    {
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param Section $section
     * @return int
     */
    public function countAssignedContents( Section $section )
    {
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param Section $section
     * @param Content $content
     * @uses ezp\Base\StorageEngine\SectionHandler::assign()
     */
    public function assign( Section $section, Content $content )
    {
        if ( $section->id === $content->section->id )
            return;
        $this->handler->sectionHandler()->assign( $section->id, $content->id );
    }

    /**
     * Deletes $section from content repository
     *
     * @param Section $section
     * @return void
     * @throws Exception\Validation
     *         if section can not be deleted
     *         because it is still assigned to some contents.
     */
    public function delete( Section $section )
    {
        if ( $this->countAssignedContents( $section ) > 0 )
        {
            throw new Validation( 'This section is assigned to some contents' );
        }
        // do the removal
    }
}
?>
