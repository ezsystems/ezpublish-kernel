<?php

/**
 * SectionService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionToSubtreeSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\CreateSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\UpdateSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\DeleteSectionSignal;

/**
 * SectionService class.
 */
class SectionService implements SectionServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\SectionService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(SectionServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Creates the a new Section in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly create section
     */
    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        $returnValue = $this->service->createSection($sectionCreateStruct);
        $this->signalDispatcher->emit(
            new CreateSectionSignal(
                [
                    'sectionId' => $returnValue->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Updates the given in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier already exists (if set in the update struct)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     * @param \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct $sectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection(Section $section, SectionUpdateStruct $sectionUpdateStruct)
    {
        $returnValue = $this->service->updateSection($section, $sectionUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateSectionSignal(
                [
                    'sectionId' => $section->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Loads a Section from its id ($sectionId).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param mixed $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection($sectionId)
    {
        return $this->service->loadSection($sectionId);
    }

    /**
     * Loads all sections, excluding the ones the current user is not allowed to read.
     *
     * @return array of {@link \eZ\Publish\API\Repository\Values\Content\Section}
     */
    public function loadSections()
    {
        return $this->service->loadSections();
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param string $sectionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier($sectionIdentifier)
    {
        return $this->service->loadSectionByIdentifier($sectionIdentifier);
    }

    /**
     * Counts the contents which $section is assigned to.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return int
     *
     * @deprecated since 6.0
     */
    public function countAssignedContents(Section $section)
    {
        return $this->service->countAssignedContents($section);
    }

    /**
     * Returns true if the given section is assigned to contents, or used in role policies, or in role assignments.
     *
     * This does not check user permissions.
     *
     * @since 6.0
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return bool
     */
    public function isSectionUsed(Section $section)
    {
        return $this->service->isSectionUsed($section);
    }

    /**
     * Assigns the content to the given section
     * this method overrides the current assigned section.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSection(ContentInfo $contentInfo, Section $section)
    {
        $returnValue = $this->service->assignSection($contentInfo, $section);
        $this->signalDispatcher->emit(
            new AssignSectionSignal(
                [
                    'contentId' => $contentInfo->id,
                    'sectionId' => $section->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Assigns the subtree to the given section.
     *
     * This method overrides the current assigned section.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSectionToSubtree(Location $location, Section $section): void
    {
        $this->service->assignSectionToSubtree($location, $section);

        $this->signalDispatcher->emit(
            new AssignSectionToSubtreeSignal(
                [
                    'locationId' => $location->id,
                    'sectionId' => $section->id,
                ]
            )
        );
    }

    /**
     * Deletes $section from content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection(Section $section)
    {
        $returnValue = $this->service->deleteSection($section);
        $this->signalDispatcher->emit(
            new DeleteSectionSignal(
                [
                    'sectionId' => $section->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Instantiates a new SectionCreateStruct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return $this->service->newSectionCreateStruct();
    }

    /**
     * Instantiates a new SectionUpdateStruct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return $this->service->newSectionUpdateStruct();
    }
}
