<?php
/**
 * File containing the FieldTypeServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\FieldType\TextLine\Type;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\FieldTypeService;

class FieldTypeServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\FieldTypeService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new FieldTypeService( $coreService, $dispatcher );
    }

    protected function getTransformationProcessorMock()
    {
        return $this->getMockForAbstractClass(
            "eZ\\Publish\\Core\\Persistence\\TransformationProcessor",
            array(),
            '',
            false,
            true,
            true
        );
    }

    public function serviceProvider()
    {
        $fieldType = new Type( $this->getTransformationProcessorMock() );

        return array(
            array(
                'getFieldTypes',
                array(),
                array( $fieldType ),
                0
            ),
            array(
                'getFieldType',
                array( 'ezstring' ),
                $fieldType,
                0
            ),
            array(
                'hasFieldType',
                array( 'ezstring' ),
                true,
                0
            )
        );
    }
}
