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
        $contentId = 42;

        $section = new Section(
            array(
                'id' => $sectionId,
                'identifier' => $sectionIdentifier,
                'name' => $sectionName,
            )
        );
        $contentInfo = $this->getContentInfo($contentId, md5('Osgiliath'));

        $sectionCreateStruct = new SectionCreateStruct();
        $sectionUpdateStruct = new SectionUpdateStruct();

        return array(
            array(
                'createSection',
                array($sectionCreateStruct),
                $section,
                1,
                SectionServiceSignal\CreateSectionSignal::class,
                array('sectionId' => $sectionId),
            ),
            array(
                'updateSection',
                array($section, $sectionUpdateStruct),
                $section,
                1,
                SectionServiceSignal\UpdateSectionSignal::class,
                array('sectionId' => $sectionId),
            ),
            array(
                'loadSection',
                array($sectionId),
                $section,
                0,
            ),
            array(
                'loadSections',
                array(),
                array($section),
                0,
            ),
            array(
                'loadSectionByIdentifier',
                array($sectionIdentifier),
                $section,
                0,
            ),
            array(
                'countAssignedContents',
                array($section),
                42,
                0,
            ),
            array(
                'isSectionUsed',
                array($section),
                true,
                0,
            ),
            array(
                'assignSection',
                array($contentInfo, $section),
                null,
                1,
                SectionServiceSignal\AssignSectionSignal::class,
                array(
                    'contentId' => $contentId,
                    'sectionId' => $sectionId,
                ),
            ),
            array(
                'deleteSection',
                array($section),
                null,
                1,
                SectionServiceSignal\DeleteSectionSignal::class,
                array(
                    'sectionId' => $sectionId,
                ),
            ),
            array(
                'newSectionCreateStruct',
                array(),
                $sectionCreateStruct,
                0,
            ),
            array(
                'newSectionUpdateStruct',
                array(),
                $sectionUpdateStruct,
                0,
            ),
        );
    }
}
