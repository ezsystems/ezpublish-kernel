<?php
/**
 * SectionService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\SectionService as SectionServiceInterface;

/**
 * SectionService class
 * @package eZ\Publish\Core\SignalSlot
 */
class SectionService implements SectionServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\SectionService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( SectionServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
    public function createSection( eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct )
    {
        $returnValue = $this->service->createSection( $sectionCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\SectionService\CreateSectionSignal( array(
                'sectionId' => $returnValue->id,
            ) )
        );
        return $returnValue;
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
    public function updateSection( eZ\Publish\API\Repository\Values\Content\Section $section, eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct $sectionUpdateStruct )
    {
        $returnValue = $this->service->updateSection( $section, $sectionUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\SectionService\UpdateSectionSignal( array(
                'sectionId' => $section->id,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadSection( $sectionId );
        return $returnValue;
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
        $returnValue = $this->service->loadSections();
        return $returnValue;
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
        $returnValue = $this->service->loadSectionByIdentifier( $sectionIdentifier );
        return $returnValue;
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return int
     */
    public function countAssignedContents( eZ\Publish\API\Repository\Values\Content\Section $section )
    {
        $returnValue = $this->service->countAssignedContents( $section );
        return $returnValue;
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
    public function assignSection( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, eZ\Publish\API\Repository\Values\Content\Section $section )
    {
        $returnValue = $this->service->assignSection( $contentInfo, $section );
        $this->signalDispatcher()->emit(
            new Signal\SectionService\AssignSectionSignal( array(
                'contentId' => $contentInfo->id,
                'sectionId' => $section->id,
            ) )
        );
        return $returnValue;
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
    public function deleteSection( eZ\Publish\API\Repository\Values\Content\Section $section )
    {
        $returnValue = $this->service->deleteSection( $section );
        $this->signalDispatcher()->emit(
            new Signal\SectionService\DeleteSectionSignal( array(
                'sectionId' => $section->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * instantiates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        $returnValue = $this->service->newSectionCreateStruct();
        return $returnValue;
    }

    /**
     * instantiates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        $returnValue = $this->service->newSectionUpdateStruct();
        return $returnValue;
    }

}

