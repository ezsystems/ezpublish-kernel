<?php
/**
 * File containing the ezp\Content\Section\Service class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Section;
use ezp\Base\Exception\NotFound,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\Forbidden,
    ezp\Base\Service as BaseService,
    ezp\Content,
    ezp\Content\Section,
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function create( Section $section )
    {
        if ( !$this->repository->canUser( 'edit', $section ) )
            throw new Forbidden( 'Section', 'edit' );

        $valueObject = $this->handler->sectionHandler()->create( $section->name, $section->identifier );
        return $section->setState( array( 'properties' => $valueObject ) );
    }

    /**
     * Updates $section in the content repository
     *
     * @param \ezp\Content\Section $section
     * @return \ezp\Content\Section
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function update( Section $section )
    {
        if ( !$this->repository->canUser( 'edit', $section ) )
            throw new Forbidden( 'Section', 'edit' );

        $this->handler->sectionHandler()->update( $section->id, $section->name, $section->identifier );
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
            throw new NotFound( 'Section', $sectionId );

        $section = $this->buildDomainObject( $valueObject );
        //if ( !$this->repository->canUser( 'view', $section ) )
            //throw new Forbidden( 'Section', 'view' );

        return $section;
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
        $valueObject = $this->handler->sectionHandler()->loadByIdentifier( $sectionIdentifier );
        if ( !$valueObject )
            throw new NotFound( 'Section', $sectionIdentifier );

        $section = $this->buildDomainObject( $valueObject );
        //if ( !$this->repository->canUser( 'view', $section ) )
            //throw new Forbidden( 'Section', 'view' );

        return $section;
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param mixed $sectionId
     * @return int
     */
    public function countAssignedContents( $sectionId )
    {
        //if ( $this->repository->getUser()->hasAccessTo( 'section', 'view' ) !== true )
            //throw new Forbidden( 'Section', 'view' );

        return $this->handler->sectionHandler()->assignmentsCount( $sectionId );
    }

    /**
     * Counts the contents which $section is assigned to
     *
     *
     * @param \ezp\Content\Section $section
     * @param Content $content
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to view provided object
     */
    public function assign( Section $section, Content $content )
    {
        if ( $section->id === $content->sectionId )
            return;// @todo Throw exception?

        if ( !$this->repository->canUser( 'assign', $section, $content ) )
            throw new Forbidden( 'Section', 'view' );

        $this->handler->sectionHandler()->assign( $section->id, $content->id );
        $content->setSection( $section );
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function delete( Section $section )
    {
        if ( !$this->repository->canUser( 'edit', $section ) )
            throw new Forbidden( 'Section', 'edit' );

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
        $section = new Concrete();
        return $section->setState( array( 'properties' => $vo ) );
    }
}
?>
