<?php
/**
 * File containing the SectionServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;

use eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\Repository\SignalSlot\SectionService;

class SectionServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\SectionService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new SectionService( $coreService, $dispatcher );
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
                'name' => $sectionName
            )
        );
        $contentInfo = $this->getContentInfo( $contentId, md5( 'Osgiliath' ) );

        $sectionCreateStruct = new SectionCreateStruct();
        $sectionUpdateStruct = new SectionUpdateStruct();

        return array(
            array(
                'createSection',
                array( $sectionCreateStruct ),
                $section,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\CreateSectionSignal',
                array( 'sectionId' => $sectionId )
            ),
            array(
                'updateSection',
                array( $section, $sectionUpdateStruct ),
                $section,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\UpdateSectionSignal',
                array( 'sectionId' => $sectionId )
            ),
            array(
                'loadSection',
                array( $sectionId ),
                $section,
                0
            ),
            array(
                'loadSections',
                array(),
                array( $section ),
                0
            ),
            array(
                'loadSectionByIdentifier',
                array( $sectionIdentifier ),
                $section,
                0
            ),
            array(
                'countAssignedContents',
                array( $section ),
                42,
                0
            ),
            array(
                'assignSection',
                array( $contentInfo, $section ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\AssignSectionSignal',
                array(
                    'contentId' => $contentId,
                    'sectionId' => $sectionId
                )
            ),
            array(
                'deleteSection',
                array( $section ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\DeleteSectionSignal',
                array(
                    'sectionId' => $sectionId
                )
            ),
            array(
                'newSectionCreateStruct',
                array(),
                $sectionCreateStruct,
                0
            ),
            array(
                'newSectionUpdateStruct',
                array(),
                $sectionUpdateStruct,
                0
            ),
        );
    }
}
