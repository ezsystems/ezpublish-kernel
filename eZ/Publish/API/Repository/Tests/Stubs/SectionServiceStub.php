<?php
/**
 * File containing the SectionServiceStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\BadStateExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\SectionService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\SectionService
 */
class SectionServiceStub implements SectionService
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * @var int
     */
    private $nextId = 0;

    /**
     * @var array
     */
    private $identifiers = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    private $sections = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo[]
     */
    private $assignedContents = array();

    /**
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;

        $this->initFromFixture();
    }

    /**
     * Creates the a new Section in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly create section
     */
    public function createSection( SectionCreateStruct $sectionCreateStruct )
    {
        if ( true !== $this->repository->hasAccess( 'section', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        if ( isset( $this->identifiers[$sectionCreateStruct->identifier] ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $section = new Section(
            array(
                'id' => ++$this->nextId,
                'name' => $sectionCreateStruct->name,
                'identifier' => $sectionCreateStruct->identifier
            )
        );

        $this->sections[$section->id] = $section;
        $this->identifiers[$section->identifier] = $section->id;

        return $section;
    }

    /**
     * Updates the given in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier already exists (if set in the update struct)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     * @param \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct $sectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection( Section $section, SectionUpdateStruct $sectionUpdateStruct )
    {
        if ( true !== $this->repository->hasAccess( 'section', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        if ( isset( $this->identifiers[$sectionUpdateStruct->identifier] ) &&
            ( $this->identifiers[$sectionUpdateStruct->identifier] !== $section->id ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $updatedSection = new Section(
            array(
                'id' => $section->id,
                'name' => ( $sectionUpdateStruct->name ? $sectionUpdateStruct->name : $section->name ),
                'identifier' => ( $sectionUpdateStruct->identifier ? $sectionUpdateStruct->identifier : $section->identifier )
            )
        );

        unset( $this->identifiers[$section->identifier] );

        $this->sections[$updatedSection->id] = $updatedSection;
        $this->identifiers[$updatedSection->identifier] = $updatedSection->id;

        return $updatedSection;
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param int $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection( $sectionId )
    {
        if ( true !== $this->repository->hasAccess( 'section', 'view' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( isset( $this->sections[$sectionId] ) )
        {
            return $this->sections[$sectionId];
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Loads all sections
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @return array of {@link \eZ\Publish\API\Repository\Values\Content\Section}
     */
    public function loadSections()
    {
        if ( true !== $this->repository->hasAccess( 'section', 'view' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return array_values( $this->sections );
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param string $sectionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier( $sectionIdentifier )
    {
        if ( true !== $this->repository->hasAccess( 'section', 'view' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( isset( $this->identifiers[$sectionIdentifier] ) )
        {
            return $this->sections[$this->identifiers[$sectionIdentifier]];
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return int
     */
    public function countAssignedContents( Section $section )
    {
        if ( isset( $this->assignedContents[$section->id] ) )
        {
            return count( $this->assignedContents[$section->id] );
        }
        return 0;
    }

    /**
     * Assigns the content to the given section
     * this method overrides the current assigned section
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSection( ContentInfo $contentInfo, Section $section )
    {
        if ( true !== $this->repository->hasAccess( 'section', 'assign' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( false === isset( $this->assignedContents[$section->id] ) )
        {
            $this->assignedContents[$section->id] = array();
        }

        // Unassign from previous section
        foreach ( $this->assignedContents as $sectionId => $assignedContents )
        {
            if ( isset( $assignedContents[$contentInfo->id] ) )
            {
                unset( $this->assignedContents[$sectionId][$contentInfo->id] );
            }
        }

        $this->assignedContents[$section->id][$contentInfo->id] = true;
    }

    /**
     * Deletes $section from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection( Section $section )
    {
        if ( false === isset( $this->sections[$section->id] ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }
        if ( isset( $this->assignedContents[$section->id] ) )
        {
            throw new BadStateExceptionStub( 'What error code should be used?' );
        }
        if ( true !== $this->repository->hasAccess( 'section', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        unset( $this->sections[$section->id], $this->identifiers[$section->identifier] );
    }

    /**
     * Instantiates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return new SectionCreateStruct();
    }

    /**
     * Instantiates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return new SectionUpdateStruct();
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @access private
     *
     * @internal
     *
     * @return void
     */
    public function rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        list(
            $this->sections,
            $this->identifiers,
            $this->assignedContents,
            $this->nextId
        ) = $this->repository->loadFixture( 'Section' );
    }
}
