<?php

/**
 * SectionService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Decorator\SectionServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\CreateSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\UpdateSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\DeleteSectionSignal;

/**
 * SectionService class.
 */
class SectionService extends SectionServiceDecorator
{
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
        parent::__construct($service);

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
                array(
                    'sectionId' => $returnValue->id,
                )
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
                array(
                    'sectionId' => $section->id,
                )
            )
        );

        return $returnValue;
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
                array(
                    'contentId' => $contentInfo->id,
                    'sectionId' => $section->id,
                )
            )
        );

        return $returnValue;
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
                array(
                    'sectionId' => $section->id,
                )
            )
        );

        return $returnValue;
    }
}
