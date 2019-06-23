<?php

/**
 * File containing the SectionServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\SectionService as APISectionService;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\SectionService;
use eZ\Publish\Core\SignalSlot\Signal\SectionService as SectionServiceSignal;

class SectionServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APISectionService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new SectionService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $sectionId = 1;
        $sectionIdentifier = 'mordor';
        $sectionName = 'Mordor';
        $locationId = 46;
        $contentId = 42;

        $section = new Section(
            [
                'id' => $sectionId,
                'identifier' => $sectionIdentifier,
                'name' => $sectionName,
            ]
        );

        $location = new Location([
            'id' => $locationId,
        ]);

        $contentInfo = $this->getContentInfo($contentId, md5('Osgiliath'));

        $sectionCreateStruct = new SectionCreateStruct();
        $sectionUpdateStruct = new SectionUpdateStruct();

        return [
            [
                'createSection',
                [$sectionCreateStruct],
                $section,
                1,
                SectionServiceSignal\CreateSectionSignal::class,
                ['sectionId' => $sectionId],
            ],
            [
                'updateSection',
                [$section, $sectionUpdateStruct],
                $section,
                1,
                SectionServiceSignal\UpdateSectionSignal::class,
                ['sectionId' => $sectionId],
            ],
            [
                'loadSection',
                [$sectionId],
                $section,
                0,
            ],
            [
                'loadSections',
                [],
                [$section],
                0,
            ],
            [
                'loadSectionByIdentifier',
                [$sectionIdentifier],
                $section,
                0,
            ],
            [
                'countAssignedContents',
                [$section],
                42,
                0,
            ],
            [
                'isSectionUsed',
                [$section],
                true,
                0,
            ],
            [
                'assignSection',
                [$contentInfo, $section],
                null,
                1,
                SectionServiceSignal\AssignSectionSignal::class,
                [
                    'contentId' => $contentId,
                    'sectionId' => $sectionId,
                ],
            ],
            [
                'assignSectionToSubtree',
                [$location, $section],
                null,
                1,
                SectionServiceSignal\AssignSectionToSubtreeSignal::class,
                [
                    'locationId' => $locationId,
                    'sectionId' => $sectionId,
                ],
            ],
            [
                'deleteSection',
                [$section],
                null,
                1,
                SectionServiceSignal\DeleteSectionSignal::class,
                [
                    'sectionId' => $sectionId,
                ],
            ],
            [
                'newSectionCreateStruct',
                [],
                $sectionCreateStruct,
                0,
            ],
            [
                'newSectionUpdateStruct',
                [],
                $sectionUpdateStruct,
                0,
            ],
        ];
    }
}
