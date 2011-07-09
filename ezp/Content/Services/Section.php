<?php
/**
 * File containing the ezp\Content\Services\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

namespace ezp\Content\Services;

/**
 * Section service, used for section operations
 *
 * @package ezp
 * @subpackage content
 */
use \ezp\Base\Exception;
class Section extends \ezp\Base\AbstractService
{
    /**
     * Creates the a new Section in the content repository
     *
     * @param \ezp\Content\Section $section
     * @return \ezp\Content\Section The newly create section
     * @throws Exception\Validation If a validation problem has been found for $section
     */
    public function create( \ezp\Content\Section $section )
    {
    }

    /**
     * Updates $section in the content repository
     *
     * @param \ezp\Content\Section $section
     * @return \ezp\Content\Section
     * @throws Exception\Validation If a validation problem has been found for $section
     */
    public function update( \ezp\Content\Section $section )
    {
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @param int $sectionId
     * @return \ezp\Content\Section
     * @throws Exception\NotFound if section could not be found
     */
    public function load( $sectionId )
    {
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @param string $sectionIdentifier
     * @return \ezp\Content\Section
     * @throws Exception\NotFound if section could not be found
     */
    public function loadByIdentifier( $sectionIdentifier )
    {
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param \ezp\Content\Section $section
     * @return int
     */
    public function countAssignedContents( \ezp\Content\Section $section )
    {
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param \ezp\Content\Section $section
     * @param \ezp\Content\Content $content
     * @uses \ezp\Base\StorageEngine\SectionHandler::assign()
     */
    public function assign( \ezp\Content\Section $section, \ezp\Content\Content $content )
    {
        if ( $section->id === $content->section->id )
            return;
        $this->handler->sectionHandler()->assign( $section->id, $content->id );
    }

    /**
     * Deletes $section from content repository
     *
     * @param \ezp\Content\Section $section
     * @return void
     * @throws Exception\Validation
     *         if section can not be deleted
     *         because it is still assigned to some contents.
     */
    public function delete( \ezp\Content\Section $section )
    {
        if ( $this->countAssignedContents( $section ) > 0 )
        {
            throw new Exception\Validation( 'This section is assigned to some contents' );
        }
        // do the removal
    }
}
?>
