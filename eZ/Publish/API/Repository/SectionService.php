<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\SectionCreateStruct;

use ezp\PublicAPI\Values\Content\Content;
use ezp\PublicAPI\Values\Content\ContentInfo;
use ezp\PublicAPI\Values\Content\Section;
use ezp\PublicAPI\Values\Content\Location;
use ezp\PublicAPI\Values\Content\SectionUpdateStruct;

/**
 * Section service, used for section operations
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface SectionService
{
    /**
     * Creates the a new Section in the content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param SectionCreateStruct $sectionCreateStruct
     *
     * @return Section The newly create section
     */
    public function createSection(SectionCreateStruct $sectionCreateStruct );

    /**
     * Updates the given in the content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException If the new identifier already exists (if set in the update struct)
     *
     * @param \ezp\PublicAPI\Values\Content\Section $section
     * @param \ezp\PublicAPI\Values\Content\SectionUpdateStruct $sectionUpdateStruct
     *
     * @return \ezp\PublicAPI\Values\Content\Section
     */
    public function updateSection( Section $section, SectionUpdateStruct $sectionUpdateStruct );

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException if section could not be found
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param int $sectionId
     *
     * @return \ezp\PublicAPI\Values\Content\Section
     */
    public function loadSection( $sectionId );

    /**
     * Loads all sections
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @return array of {@link \ezp\PublicAPI\Values\Content\Section}
     */
    public function loadSections();

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException if section could not be found
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param string $sectionIdentifier
     *
     * @return \ezp\PublicAPI\Values\Content\Section
     */
    public function loadSectionByIdentifier( $sectionIdentifier );

    /**
     * Counts the contents which $section is assigned to
     *
     * @param \ezp\PublicAPI\Values\Content\Section $section
     *
     * @return int
     */
    public function countAssignedContents( Section $section );

    /**
     * assigns the content to the given section
     * this method overrides the current assigned section
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param \ezp\PublicAPI\Values\Content\Section $section
     */
    public function assignSection( ContentInfo $contentInfo, Section $section );


    /**
     * Deletes $section from content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException If the specified section is not found
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws \ezp\PublicAPI\Exceptions\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \ezp\PublicAPI\Values\Content\Section $section
     */
    public function deleteSection( Section $section );

    /**
     * instanciates a new SectionCreateStruct
     * 
     * @return \ezp\PublicAPI\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct();
    
    /**
     * instanciates a new SectionUpdateStruct
     * 
     * @return \ezp\PublicAPI\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct();
    
}
