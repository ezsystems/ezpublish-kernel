<?php
/**
 * File containing the eZ\Publish\Core\Repository\Permission\SectionService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository\Permission
 */

namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Content\Section\Handler;
use eZ\Publish\SPI\Persistence\Content\Section as SPISection;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Exception;

/**
 * Section service, used for section operations
 *
 * @package eZ\Publish\Core\Repository\Permission
 */
class SectionService implements SectionServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $permissionsService;

    /**
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $innerSectionService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\SectionService $innerSectionService
     * @param PermissionsService $permissionsService
     */
    public function __construct(
        SectionServiceInterface $innerSectionService,
        PermissionsService $permissionsService
    )
    {
        $this->innerSectionService = $innerSectionService;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Creates a new Section in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly created section
     */
    public function createSection( SectionCreateStruct $sectionCreateStruct )
    {
        if ( $this->permissionsService->hasAccess( 'section', 'edit' ) !== true )
            throw new UnauthorizedException( 'section', 'edit' );

        return $this->innerSectionService->createSection( $sectionCreateStruct );
    }

    /**
     * Updates the given section in the content repository
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
        if ( $this->permissionsService->canUser( 'section', 'edit', $section ) !== true )
            throw new UnauthorizedException( 'section', 'edit' );

        return $this->innerSectionService->updateSection( $section, $sectionUpdateStruct );
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param mixed $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection( $sectionId )
    {
        if ( $this->permissionsService->hasAccess( 'section', 'view' ) !== true )
            throw new UnauthorizedException( 'section', 'view' );

        return $this->innerSectionService->loadSection( $sectionId );
    }

    /**
     * Loads all sections
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    public function loadSections()
    {
        if ( $this->permissionsService->hasAccess( 'section', 'view' ) !== true )
            throw new UnauthorizedException( 'section', 'view' );

        return $this->innerSectionService->loadSections();
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
        if ( $this->permissionsService->hasAccess( 'section', 'view' ) !== true )
            throw new UnauthorizedException( 'section', 'view' );

        return $this->innerSectionService->loadSectionByIdentifier( $sectionIdentifier );
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return int
     */
    public function countAssignedContents( Section $section )
    {
        if ( $this->permissionsService->hasAccess( 'section', 'view' ) !== true )
            throw new UnauthorizedException( 'section', 'view' );

        return $this->innerSectionService->countAssignedContents( $section );
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
        if ( $this->permissionsService->canUser( 'section', 'assign', $contentInfo, $section ) !== true )
        {
            throw new UnauthorizedException(
                'section', 'assign',
                array(
                    'name' => $section->name,
                    'content-name' => $contentInfo->name
                )
            );
        }

        return $this->innerSectionService->assignSection( $contentInfo, $section );
    }

    /**
     * Deletes $section from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection( Section $section )
    {
        if ( $this->permissionsService->canUser( 'section', 'edit', $section ) !== true )
            throw new UnauthorizedException( 'section', 'edit', array( 'name' => $section->name ) );

        return $this->innerSectionService->deleteSection( $section );
    }

    /**
     * Instantiates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return $this->innerSectionService->newSectionCreateStruct();
    }

    /**
     * Instantiates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return $this->innerSectionService->newSectionUpdateStruct();
    }
}
