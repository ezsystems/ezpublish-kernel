<?php
/**
 * File containing the SectionService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client;

use \eZ\Publish\API\Repository\Values\Content\ContentInfo;
use \eZ\Publish\API\Repository\Values\Content\Section;
use \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;


/**
 * bed implementation of the {@link \eZ\Publish\API\Repository\SectionService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\SectionService
 */
class SectionService implements \eZ\Publish\API\Repository\SectionService
{
    /**
     * @var \eZ\Publish\API\REST\Client\Repository
     */
    private $repository;

    /**
     * @param \eZ\Publish\API\REST\Client\Repository $repository
     */
    public function __construct( Repository $repository )
    {
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * assigns the content to the given section
     * this method overrides the current assigned section
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSection( ContentInfo $contentInfo, Section $section )
    {
        throw new \Exception( "@TODO: Implement." );
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
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * instanciates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * instanciates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        throw new \Exception( "@TODO: Implement." );
    }
}
