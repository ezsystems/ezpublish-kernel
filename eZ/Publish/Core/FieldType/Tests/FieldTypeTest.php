<?php

/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\SPI\FieldType\Tests\FieldTypeTest as BaseFieldTypeTest;
use eZ\Publish\Core\Persistence\TransformationProcessor;

abstract class FieldTypeTest extends BaseFieldTypeTest
{
    /**
     * @return \eZ\Publish\Core\Persistence\TransformationProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTransformationProcessorMock()
    {
        return $this->getMockForAbstractClass(
            TransformationProcessor::class,
            [],
            '',
            false,
            true,
            true,
            ['transform', 'transformByGroup']
        );
    }
}
