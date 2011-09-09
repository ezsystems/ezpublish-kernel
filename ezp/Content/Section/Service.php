<?php
/**
 * File containing the ezp\Content\Section\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Section;
use ezp\Base\Exception\NotFound,
    ezp\Base\Exception\Logic,
    ezp\Base\Service as BaseService,
    ezp\Content,
    ezp\Content\Section as SectionObject,
    ezp\Persistence\ValueObject;

/**
 * Section service, used for section operations
 */
class Service extends BaseService
{
    /**
     * Creates the a new Section in the content repository
     *
     * @param \ezp\Content\Section $section
     * @return \ezp\Content\Section The newly create section
     * @todo Should api be adjusted to take name and identifier like handler instead of object?
     */
    public function create( SectionObject $section )
    {
        $valueObject = $this->handler->sectionHandler()->create( $section->name, $section->identifier );
        return $section->setState( array( 'properties' => $valueObject ) );
    }

    /**
     * Updates $section in the content repository
     *
     * @param \ezp\Content\Section $section
     * @return \ezp\Content\Section
     * @throws Exception\Validation If a validation problem has been found for $section
     */
    public function update( SectionObject $section )
    {
        $this->handler->sectionHandler()->update( $section->id, $section->identifier, $section->name );
        return $section;
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @param int $sectionId
     * @return \ezp\Content\Section|null
     * @throws \ezp\Base\Exception\NotFound if section could not be found
     */
    public function load( $sectionId )
    {
        $valueObject = $this->handler->sectionHandler()->load( $sectionId );
        if ( !$valueObject )
            throw new NotFound( 'section', $sectionId );
        return $this->buildDomainObject( $valueObject );
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @param string $sectionIdentifier
     * @return \ezp\Content\Section
     * @throws \ezp\Base\Exception\NotFound if section could not be found
     */
    public function loadByIdentifier( $sectionIdentifier )
    {
        throw new NotFound( 'section', $sectionIdentifier );
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param int $sectionId
     * @return int
     */
    public function countAssignedContents( $sectionId )
    {
        return 0;
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @todo should this function assign section object to content->section?
     *       What if that is already done but nothing is saved, then first line here will fail.
     *
     * @param \ezp\Content\Section $section
     * @param Content $content
     * @uses \ezp\Base\StorageEngine\SectionHandler::assign()
     */
    public function assign( SectionObject $section, Content $content )
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
     * @throws \ezp\Base\Exception\Logic
     *         if section can not be deleted
     *         because it is still assigned to some contents.
     * @throws \ezp\Base\Exception\NotFound If the specified section is not found
     */
    public function delete( SectionObject $section )
    {
        if ( $this->countAssignedContents( $section->id ) > 0 )
        {
            throw new Logic(
                "delete( {$section->id} )",
                "section can not be deleted as its assigned to content objects."
            );
        }
        $this->handler->sectionHandler()->delete( $section->id );
    }

    /**
     * Build DO based on VO
     *
     * @param \ezp\Persistence\ValueObject $vo
     * @return \ezp\Content\Section
     */
    protected function buildDomainObject( ValueObject $vo )
    {
        $section = new SectionObject();
        return $section->setState( array( 'properties' => $vo ) );
    }
}
?>
