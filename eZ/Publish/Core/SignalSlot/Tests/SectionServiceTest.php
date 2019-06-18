<?php

/**
 * File containing the SectionServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\SectionService;

class SectionServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\SectionService'
        );
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
        $contentId = 42;

        $section = new Section(
            [
                'id' => $sectionId,
                'identifier' => $sectionIdentifier,
                'name' => $sectionName,
            ]
        );
        $contentInfo = $this->getContentInfo($contentId, md5('Osgiliath'));

        $sectionCreateStruct = new SectionCreateStruct();
        $sectionUpdateStruct = new SectionUpdateStruct();

        return [
            [
                'createSection',
                [$sectionCreateStruct],
                $section,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\SectionService\CreateSectionSignal',
                ['sectionId' => $sectionId],
            ],
            [
                'updateSection',
                [$section, $sectionUpdateStruct],
                $section,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\SectionService\UpdateSectionSignal',
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
                'eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal',
                [
                    'contentId' => $contentId,
                    'sectionId' => $sectionId,
                ],
            ],
            [
                'deleteSection',
                [$section],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\SectionService\DeleteSectionSignal',
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
