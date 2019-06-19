<?php

/**
 * File containing the FieldTypeServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\FieldTypeService as APIFieldTypeService;
use eZ\Publish\Core\FieldType\TextLine\Type;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\FieldTypeService;
use eZ\Publish\Core\Persistence\TransformationProcessor;

class FieldTypeServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIFieldTypeService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new FieldTypeService($coreService, $dispatcher);
    }

    protected function getTransformationProcessorMock()
    {
        return $this->getMockForAbstractClass(
            TransformationProcessor::class,
            [],
            '',
            false,
            true,
            true
        );
    }

    public function serviceProvider()
    {
        $fieldType = new Type($this->getTransformationProcessorMock());

        return [
            [
                'getFieldTypes',
                [],
                [$fieldType],
                0,
            ],
            [
                'getFieldType',
                ['ezstring'],
                $fieldType,
                0,
            ],
            [
                'hasFieldType',
                ['ezstring'],
                true,
                0,
            ],
        ];
    }
}
